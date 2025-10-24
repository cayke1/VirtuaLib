<?php

require_once __DIR__ . '/CloudflareR2Service.php';

/**
 * Classe utilitária para gerenciar uploads de imagens
 * Agora integrada com Cloudflare R2
 */
class ImageUploader
{
    private $uploadDir;
    private $allowedTypes = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
    private $maxFileSize = 5 * 1024 * 1024; // 5MB
    private $r2Service;
    private $useR2;

    public function __construct($uploadDir = null, $useR2 = true)
    {
        $this->uploadDir = $uploadDir ?: __DIR__ . '/../../public/uploads/covers/';
        $this->useR2 = $useR2;
        
        // Criar diretório local se não existir (para fallback)
        if (!is_dir($this->uploadDir)) {
            mkdir($this->uploadDir, 0755, true);
        }

        // Inicializar serviço R2 se habilitado
        if ($this->useR2) {
            try {
                $this->r2Service = new CloudflareR2Service();
            } catch (Exception $e) {
                error_log("Erro ao inicializar CloudflareR2Service: " . $e->getMessage());
                $this->useR2 = false;
            }
        }
    }

    /**
     * Upload de imagem com validação
     */
    public function uploadImage($file, $bookId = null)
    {
        // Verificar se há arquivo
        if (!isset($file) || $file['error'] !== UPLOAD_ERR_OK) {
            return ['success' => false, 'message' => 'Nenhum arquivo enviado ou erro no upload'];
        }

        // Verificar tamanho do arquivo
        if ($file['size'] > $this->maxFileSize) {
            return ['success' => false, 'message' => 'Arquivo muito grande. Máximo 5MB'];
        }

        // Verificar tipo do arquivo
        $fileExtension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!in_array($fileExtension, $this->allowedTypes)) {
            return ['success' => false, 'message' => 'Tipo de arquivo não permitido. Use: ' . implode(', ', $this->allowedTypes)];
        }

        // Verificar se é realmente uma imagem
        $imageInfo = getimagesize($file['tmp_name']);
        if ($imageInfo === false) {
            return ['success' => false, 'message' => 'Arquivo não é uma imagem válida'];
        }

        // Gerar nome único para o arquivo
        $fileName = $this->generateFileName($bookId, $fileExtension);
        
        // Tentar upload para R2 primeiro
        if ($this->useR2 && $this->r2Service) {
            $result = $this->uploadToR2($file, $fileName, $imageInfo);
            return $result;
        }
        
        // Fallback para upload local
        return $this->uploadLocally($file, $fileName);
    }

    /**
     * Upload para Cloudflare R2
     */
    private function uploadToR2($file, $fileName, $imageInfo)
    {
        try {
            // Gerar chave única para R2
            $key = $this->r2Service->generateUniqueKey('covers', pathinfo($fileName, PATHINFO_EXTENSION));
            
            // Determinar content type
            $contentType = $imageInfo['mime'] ?? 'image/jpeg';
            
            // Redimensionar imagem se necessário antes do upload
            $tempFile = $this->resizeImageIfNeeded($file['tmp_name']);
            $uploadFile = $tempFile ?: $file['tmp_name'];
            
            // Upload para R2
            $result = $this->r2Service->uploadFile($uploadFile, $key, $contentType);
            error_log("Upload para R2 resultado: " . print_r($result, true));
            echo "Upload para R2 resultado: " . print_r($result, true);
            
            // Limpar arquivo temporário se foi criado
            if ($tempFile && $tempFile !== $file['tmp_name']) {
                unlink($tempFile);
            }
            
            if ($result['success']) {
                return [
                    'success' => true,
                    'path' => $result['url'],
                    'key' => $key,
                    'message' => 'Imagem enviada para R2 com sucesso'
                ];
            } else {
                // Se falhou no R2, tentar upload local como fallback
                error_log("Falha no upload para R2: " . $result['message']);
                return $this->uploadLocally($file, $fileName);
            }
        } catch (Exception $e) {
            error_log("Erro no upload para R2: " . $e->getMessage());
            return $this->uploadLocally($file, $fileName);
        }
    }

    /**
     * Upload local (fallback)
     */
    private function uploadLocally($file, $fileName)
    {
        $filePath = $this->uploadDir . $fileName;

        // Mover arquivo para diretório de destino
        if (move_uploaded_file($file['tmp_name'], $filePath)) {
            // Retornar caminho relativo para o banco de dados
            $relativePath = '/uploads/covers/' . $fileName;
            return ['success' => true, 'path' => $relativePath, 'message' => 'Imagem enviada localmente com sucesso'];
        } else {
            return ['success' => false, 'message' => 'Erro ao salvar arquivo'];
        }
    }

    /**
     * Gerar nome único para o arquivo
     */
    private function generateFileName($bookId, $extension)
    {
        $timestamp = time();
        $randomString = bin2hex(random_bytes(8));
        
        if ($bookId) {
            return "book_{$bookId}_{$timestamp}_{$randomString}.{$extension}";
        } else {
            return "book_{$timestamp}_{$randomString}.{$extension}";
        }
    }

    /**
     * Deletar imagem antiga
     */
    public function deleteImage($imagePath)
    {
        if (empty($imagePath)) {
            return true;
        }

        // Se é uma URL do R2, deletar do R2
        if ($this->isR2Url($imagePath)) {
            return $this->deleteFromR2($imagePath);
        }

        // Se é um caminho local, deletar localmente
        return $this->deleteLocally($imagePath);
    }

    /**
     * Verificar se é uma URL do R2
     */
    private function isR2Url($path)
    {
        if (!$this->r2Service) {
            return false;
        }
        
        $publicUrl = $this->r2Service->getPublicUrl('');
        return strpos($path, rtrim($publicUrl, '/')) === 0;
    }

    /**
     * Deletar do R2
     */
    private function deleteFromR2($imagePath)
    {
        if (!$this->r2Service) {
            return false;
        }

        try {
            $key = $this->r2Service->extractKeyFromUrl($imagePath);
            if (!$key) {
                error_log("Não foi possível extrair chave da URL: " . $imagePath);
                return false;
            }

            $result = $this->r2Service->deleteFile($key);
            if (!$result['success']) {
                error_log("Erro ao deletar do R2: " . $result['message']);
            }
            return $result['success'];
        } catch (Exception $e) {
            error_log("Exceção ao deletar do R2: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Deletar localmente
     */
    private function deleteLocally($imagePath)
    {
        // Converter caminho relativo para absoluto
        $fullPath = __DIR__ . '/../../public' . $imagePath;
        
        if (file_exists($fullPath)) {
            return unlink($fullPath);
        }
        
        return true; // Arquivo não existe, consideramos sucesso
    }

    /**
     * Validar e redimensionar imagem se necessário
     * Retorna caminho do arquivo redimensionado ou null se não foi necessário redimensionar
     */
    public function resizeImageIfNeeded($filePath, $maxWidth = 400, $maxHeight = 600)
    {
        $imageInfo = getimagesize($filePath);
        if ($imageInfo === false) {
            return null;
        }

        $originalWidth = $imageInfo[0];
        $originalHeight = $imageInfo[1];
        $mimeType = $imageInfo['mime'];

        // Se a imagem já é menor que o máximo, não redimensionar
        if ($originalWidth <= $maxWidth && $originalHeight <= $maxHeight) {
            return null;
        }

        // Calcular novas dimensões mantendo proporção
        $ratio = min($maxWidth / $originalWidth, $maxHeight / $originalHeight);
        $newWidth = intval($originalWidth * $ratio);
        $newHeight = intval($originalHeight * $ratio);

        // Criar imagem de origem
        switch ($mimeType) {
            case 'image/jpeg':
                $sourceImage = imagecreatefromjpeg($filePath);
                break;
            case 'image/png':
                $sourceImage = imagecreatefrompng($filePath);
                break;
            case 'image/gif':
                $sourceImage = imagecreatefromgif($filePath);
                break;
            case 'image/webp':
                $sourceImage = imagecreatefromwebp($filePath);
                break;
            default:
                return null;
        }

        if (!$sourceImage) {
            return null;
        }

        // Criar nova imagem redimensionada
        $resizedImage = imagecreatetruecolor($newWidth, $newHeight);
        
        // Preservar transparência para PNG e GIF
        if ($mimeType === 'image/png' || $mimeType === 'image/gif') {
            imagealphablending($resizedImage, false);
            imagesavealpha($resizedImage, true);
            $transparent = imagecolorallocatealpha($resizedImage, 255, 255, 255, 127);
            imagefilledrectangle($resizedImage, 0, 0, $newWidth, $newHeight, $transparent);
        }

        // Redimensionar
        imagecopyresampled($resizedImage, $sourceImage, 0, 0, 0, 0, $newWidth, $newHeight, $originalWidth, $originalHeight);

        // Criar arquivo temporário para a imagem redimensionada
        $tempFile = tempnam(sys_get_temp_dir(), 'resized_image_');
        if (!$tempFile) {
            imagedestroy($sourceImage);
            imagedestroy($resizedImage);
            return null;
        }

        // Salvar imagem redimensionada no arquivo temporário
        $result = false;
        switch ($mimeType) {
            case 'image/jpeg':
                $result = imagejpeg($resizedImage, $tempFile, 85);
                break;
            case 'image/png':
                $result = imagepng($resizedImage, $tempFile, 8);
                break;
            case 'image/gif':
                $result = imagegif($resizedImage, $tempFile);
                break;
            case 'image/webp':
                $result = imagewebp($resizedImage, $tempFile, 85);
                break;
        }

        // Limpar memória
        imagedestroy($sourceImage);
        imagedestroy($resizedImage);

        return $result ? $tempFile : null;
    }
}

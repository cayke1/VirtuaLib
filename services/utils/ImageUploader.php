<?php

/**
 * Classe utilitária para gerenciar uploads de imagens
 */
class ImageUploader
{
    private $uploadDir;
    private $allowedTypes = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
    private $maxFileSize = 5 * 1024 * 1024; // 5MB

    public function __construct($uploadDir = null)
    {
        $this->uploadDir = $uploadDir ?: __DIR__ . '/../../public/uploads/covers/';
        
        // Criar diretório se não existir
        if (!is_dir($this->uploadDir)) {
            mkdir($this->uploadDir, 0755, true);
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
        $filePath = $this->uploadDir . $fileName;

        // Mover arquivo para diretório de destino
        if (move_uploaded_file($file['tmp_name'], $filePath)) {
            // Retornar caminho relativo para o banco de dados
            $relativePath = '/uploads/covers/' . $fileName;
            return ['success' => true, 'path' => $relativePath, 'message' => 'Imagem enviada com sucesso'];
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

        // Converter caminho relativo para absoluto
        $fullPath = __DIR__ . '/../../public' . $imagePath;
        
        if (file_exists($fullPath)) {
            return unlink($fullPath);
        }
        
        return true; // Arquivo não existe, consideramos sucesso
    }

    /**
     * Validar e redimensionar imagem se necessário
     */
    public function resizeImageIfNeeded($filePath, $maxWidth = 400, $maxHeight = 600)
    {
        $imageInfo = getimagesize($filePath);
        if ($imageInfo === false) {
            return false;
        }

        $originalWidth = $imageInfo[0];
        $originalHeight = $imageInfo[1];
        $mimeType = $imageInfo['mime'];

        // Se a imagem já é menor que o máximo, não redimensionar
        if ($originalWidth <= $maxWidth && $originalHeight <= $maxHeight) {
            return true;
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
                return false;
        }

        if (!$sourceImage) {
            return false;
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

        // Salvar imagem redimensionada
        $result = false;
        switch ($mimeType) {
            case 'image/jpeg':
                $result = imagejpeg($resizedImage, $filePath, 85);
                break;
            case 'image/png':
                $result = imagepng($resizedImage, $filePath, 8);
                break;
            case 'image/gif':
                $result = imagegif($resizedImage, $filePath);
                break;
            case 'image/webp':
                $result = imagewebp($resizedImage, $filePath, 85);
                break;
        }

        // Limpar memória
        imagedestroy($sourceImage);
        imagedestroy($resizedImage);

        return $result;
    }
}

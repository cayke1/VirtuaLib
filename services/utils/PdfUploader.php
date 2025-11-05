<?php
require_once __DIR__ . '/CloudflareR2Service.php';

class PdfUploader
{
    private $uploadDir;
    private $maxFileSize = 20 * 1024 * 1024; // 20 MB
    private $allowedTypes = ['pdf'];
    private $r2Service;
    private $useR2;

    public function __construct($uploadDir = null, $useR2 = true)
    {
        $this->uploadDir = $uploadDir ?: __DIR__ . '/../../public/uploads/pdfs/';
        $this->useR2 = $useR2;

        // Cria o diretório local se não existir
        if (!is_dir($this->uploadDir)) {
            mkdir($this->uploadDir, 0755, true);
        }

        // Tenta inicializar o serviço da Cloudflare R2
        if ($this->useR2) {
            try {
                $this->r2Service = new CloudflareR2Service();
            } catch (Exception $e) {
                error_log("Erro ao inicializar CloudflareR2Service: " . $e->getMessage());
                $this->useR2 = false; // fallback automático
            }
        }
    }

    public function uploadPdf($file, $bookId = null)
    {
        // Verifica se o arquivo foi enviado corretamente
        if (!isset($file) || !isset($file['error']) || $file['error'] !== UPLOAD_ERR_OK) {
            return ['success' => false, 'message' => 'Nenhum arquivo enviado ou erro no upload.'];
        }

        // Validação da extensão
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!in_array($ext, $this->allowedTypes)) {
            return ['success' => false, 'message' => 'Somente arquivos PDF são permitidos.'];
        }

        // Validação do tamanho
        if ($file['size'] > $this->maxFileSize) {
            return ['success' => false, 'message' => 'Arquivo muito grande (máximo de 20MB).'];
        }

        // Gera nome único pro arquivo
        $fileName = $this->generateFileName($bookId, $ext);

        // Tentativa de upload na Cloudflare R2
        if ($this->useR2 && $this->r2Service) {
            try {
                $key = $this->r2Service->generateUniqueKey('pdfs', 'pdf');
                $result = $this->r2Service->uploadFile($file['tmp_name'], $key, 'application/pdf');

                if ($result['success'] && !empty($result['url'])) {
                    return [
                        'success' => true,
                        'path' => $result['url'],
                        'key' => $key,
                        'storage' => 'r2'
                    ];
                } else {
                    error_log("Falha no upload R2: " . json_encode($result));
                }
            } catch (Exception $e) {
                error_log("Erro no upload R2: " . $e->getMessage());
            }
        }

        // Fallback para upload local
        $dest = $this->uploadDir . $fileName;
        if (move_uploaded_file($file['tmp_name'], $dest)) {
            return [
                'success' => true,
                'path' => '/uploads/pdfs/' . $fileName,
                'storage' => 'local'
            ];
        }

        return ['success' => false, 'message' => 'Erro ao salvar o arquivo PDF.'];
    }

    private function generateFileName($bookId, $ext)
    {
        $ts = time();
        $rnd = bin2hex(random_bytes(6));
        return $bookId ? "book_{$bookId}_{$ts}_{$rnd}.{$ext}" : "{$ts}_{$rnd}.{$ext}";
    }

    public function deletePdf($path)
    {
        try {
            // Deletar do R2 se for URL do R2
            if ($this->useR2 && $this->r2Service && strpos($path, 'cloudflare') !== false) {
                // Aqui você pode extrair a key da URL caso precise deletar no bucket
                return true; // ainda sem integração completa
            }

            // Deletar arquivo local
            $filePath = __DIR__ . '/../../public' . $path;
            if (file_exists($filePath)) {
                return unlink($filePath);
            }

            return true;
        } catch (Exception $e) {
            error_log("Erro ao deletar PDF: " . $e->getMessage());
            return false;
        }
    }
}

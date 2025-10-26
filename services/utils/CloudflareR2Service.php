<?php

require_once __DIR__ . '/LoadEnv.php';

// Tentar diferentes caminhos para o autoloader
$autoloadPaths = [
    __DIR__ . '/../../vendor/autoload.php',
    __DIR__ . '/../../../vendor/autoload.php',
    '/var/www/html/vendor/autoload.php'
];

$autoloadFound = false;
foreach ($autoloadPaths as $path) {
    if (file_exists($path)) {
        require_once $path;
        $autoloadFound = true;
        break;
    }
}

if (!$autoloadFound) {
    throw new Exception('Autoloader do Composer não encontrado. Execute: composer install');
}

use Aws\S3\S3Client;
use Aws\Exception\AwsException;

/**
 * Serviço para integração com Cloudflare R2
 */
class CloudflareR2Service
{
    private $s3Client;
    private $bucketName;
    private $publicUrl;

    public function __construct()
    {
        LoadEnv::loadAll(__DIR__."../../.env");
        
        $this->bucketName = $_ENV['R2_BUCKET'] ?? '';
        $this->publicUrl = $_ENV['CDN_URL'] ?? '';
        
        if (empty($this->bucketName) || empty($this->publicUrl)) {
            throw new Exception('Configurações do R2 não encontradas. Verifique R2_BUCKET e CDN_URL no .env');
        }

        $this->initializeS3Client();
    }

    /**
     * Inicializar cliente S3 para R2
     */
    private function initializeS3Client()
    {
        $this->s3Client = new S3Client([
            'version' => 'latest',
            'region' => 'auto',
            'endpoint' => $_ENV['R2_ENDPOINT'] ?? 'https://your-account-id.r2.cloudflarestorage.com',
            'credentials' => [
                'key' => $_ENV['R2_ACCESS_KEY_ID'] ?? '',
                'secret' => $_ENV['R2_SECRET_ACCESS_KEY'] ?? '',
            ],
            'use_path_style_endpoint' => true,
        ]);
    }

    /**
     * Upload de arquivo para R2
     */
    public function uploadFile($filePath, $key, $contentType = null)
    {
        try {
            $params = [
                'Bucket' => $this->bucketName,
                'Key' => $key,
                'SourceFile' => $filePath,
                'ACL' => 'public-read',
            ];

            if ($contentType) {
                $params['ContentType'] = $contentType;
            }

            $result = $this->s3Client->putObject($params);
            
            return [
                'success' => true,
                'url' => $this->publicUrl . '/' . $key,
                'key' => $key,
                'etag' => $result['ETag'] ?? null
            ];
        } catch (AwsException $e) {
            error_log("Erro ao fazer upload para R2: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Erro ao fazer upload para R2: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Upload de dados binários para R2
     */
    public function uploadData($data, $key, $contentType = null)
    {
        try {
            $params = [
                'Bucket' => $this->bucketName,
                'Key' => $key,
                'Body' => $data,
                'ACL' => 'public-read',
            ];

            if ($contentType) {
                $params['ContentType'] = $contentType;
            }

            $result = $this->s3Client->putObject($params);
            
            return [
                'success' => true,
                'url' => $this->publicUrl . '/' . $key,
                'key' => $key,
                'etag' => $result['ETag'] ?? null
            ];
        } catch (AwsException $e) {
            error_log("Erro ao fazer upload de dados para R2: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Erro ao fazer upload para R2: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Deletar arquivo do R2
     */
    public function deleteFile($key)
    {
        try {
            $this->s3Client->deleteObject([
                'Bucket' => $this->bucketName,
                'Key' => $key,
            ]);

            return [
                'success' => true,
                'message' => 'Arquivo deletado com sucesso'
            ];
        } catch (AwsException $e) {
            error_log("Erro ao deletar arquivo do R2: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Erro ao deletar arquivo do R2: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Verificar se arquivo existe no R2
     */
    public function fileExists($key)
    {
        try {
            $this->s3Client->headObject([
                'Bucket' => $this->bucketName,
                'Key' => $key,
            ]);
            return true;
        } catch (AwsException $e) {
            if ($e->getAwsErrorCode() === 'NotFound') {
                return false;
            }
            error_log("Erro ao verificar arquivo no R2: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Obter URL pública do arquivo
     */
    public function getPublicUrl($key)
    {
        return $this->publicUrl . '/' . $key;
    }

    /**
     * Extrair chave da URL pública
     */
    public function extractKeyFromUrl($url)
    {
        if (strpos($url, $this->publicUrl) === 0) {
            return substr($url, strlen($this->publicUrl) + 1);
        }
        return null;
    }

    /**
     * Gerar chave única para arquivo
     */
    public function generateUniqueKey($prefix = 'uploads', $extension = '')
    {
        $timestamp = time();
        $randomString = bin2hex(random_bytes(8));
        
        $key = $prefix . '/' . $timestamp . '_' . $randomString;
        
        if ($extension) {
            $key .= '.' . ltrim($extension, '.');
        }
        
        return $key;
    }

    /**
     * Obter informações do arquivo
     */
    public function getFileInfo($key)
    {
        try {
            $result = $this->s3Client->headObject([
                'Bucket' => $this->bucketName,
                'Key' => $key,
            ]);

            return [
                'success' => true,
                'size' => $result['ContentLength'] ?? 0,
                'contentType' => $result['ContentType'] ?? '',
                'lastModified' => $result['LastModified'] ?? null,
                'etag' => $result['ETag'] ?? null
            ];
        } catch (AwsException $e) {
            if ($e->getAwsErrorCode() === 'NotFound') {
                return [
                    'success' => false,
                    'message' => 'Arquivo não encontrado'
                ];
            }
            error_log("Erro ao obter informações do arquivo no R2: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Erro ao obter informações do arquivo: ' . $e->getMessage()
            ];
        }
    }
}

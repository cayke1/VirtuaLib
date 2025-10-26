<?php

/**
 * Utilitário para lidar com URLs de imagens
 * Suporta tanto URLs locais quanto URLs do Cloudflare R2
 */
class ImageUrlHelper
{
    private static $r2Service = null;

    /**
     * Inicializar serviço R2 se disponível
     */
    private static function initR2Service()
    {
        if (self::$r2Service === null) {
            try {
                require_once __DIR__ . '/CloudflareR2Service.php';
                self::$r2Service = new CloudflareR2Service();
            } catch (Exception $e) {
                self::$r2Service = false;
            }
        }
    }

    /**
     * Obter URL completa da imagem
     * Se for uma URL do R2, retorna como está
     * Se for um caminho local, adiciona o prefixo correto
     */
    public static function getImageUrl($imagePath)
    {
        if (empty($imagePath)) {
            return null;
        }

        // Se já é uma URL completa (http/https), retornar como está
        if (strpos($imagePath, 'http://') === 0 || strpos($imagePath, 'https://') === 0) {
            return $imagePath;
        }

        // Se começa com /public/, remover o prefixo /public
        if (strpos($imagePath, '/public/') === 0) {
            $imagePath = substr($imagePath, 7); // Remove '/public'
        }

        // Se começa com /uploads/, adicionar /public
        if (strpos($imagePath, '/uploads/') === 0) {
            return '/public' . $imagePath;
        }

        // Se não tem prefixo, assumir que é um caminho local
        return '/public/uploads/covers/' . ltrim($imagePath, '/');
    }

    /**
     * Verificar se uma URL é do R2
     */
    public static function isR2Url($url)
    {
        self::initR2Service();
        
        if (!self::$r2Service) {
            return false;
        }

        try {
            $publicUrl = self::$r2Service->getPublicUrl('');
            return strpos($url, rtrim($publicUrl, '/')) === 0;
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Obter URL de fallback para imagem
     * Retorna uma imagem placeholder se a imagem não estiver disponível
     */
    public static function getFallbackImageUrl()
    {
        return '/public/css/placeholder-book.svg';
    }

    /**
     * Gerar atributos HTML para imagem com fallback
     */
    public static function getImageAttributes($imagePath, $alt = '', $class = '')
    {
        $url = self::getImageUrl($imagePath);
        $attributes = [];

        if ($url) {
            $attributes['src'] = $url;
        } else {
            $attributes['src'] = self::getFallbackImageUrl();
        }

        if ($alt) {
            $attributes['alt'] = htmlspecialchars($alt);
        }

        if ($class) {
            $attributes['class'] = htmlspecialchars($class);
        }

        // Adicionar atributos de fallback
        $attributes['onerror'] = "this.src='" . self::getFallbackImageUrl() . "'";

        return $attributes;
    }

    /**
     * Gerar tag HTML completa para imagem
     */
    public static function getImageTag($imagePath, $alt = '', $class = '', $additionalAttributes = [])
    {
        $attributes = self::getImageAttributes($imagePath, $alt, $class);
        $attributes = array_merge($attributes, $additionalAttributes);

        $html = '<img';
        foreach ($attributes as $key => $value) {
            $html .= ' ' . $key . '="' . htmlspecialchars($value) . '"';
        }
        $html .= '>';

        return $html;
    }

    /**
     * Obter informações da imagem (tamanho, tipo, etc.)
     * Funciona tanto para URLs locais quanto do R2
     */
    public static function getImageInfo($imagePath)
    {
        $url = self::getImageUrl($imagePath);
        
        if (!$url) {
            return null;
        }

        // Se é uma URL do R2, usar o serviço R2
        if (self::isR2Url($url)) {
            self::initR2Service();
            if (self::$r2Service) {
                $key = self::$r2Service->extractKeyFromUrl($url);
                if ($key) {
                    return self::$r2Service->getFileInfo($key);
                }
            }
            return null;
        }

        // Para URLs locais, usar getimagesize
        $localPath = $_SERVER['DOCUMENT_ROOT'] . $url;
        if (file_exists($localPath)) {
            $info = getimagesize($localPath);
            if ($info) {
                return [
                    'success' => true,
                    'width' => $info[0],
                    'height' => $info[1],
                    'contentType' => $info['mime'],
                    'size' => filesize($localPath)
                ];
            }
        }

        return null;
    }
}

<?php

class TextUtils
{
    /**
     * Limita o número de caracteres de um texto
     * 
     * @param string $text - Texto a ser limitado
     * @param int $limit - Número máximo de caracteres
     * @param string $suffix - Sufixo a ser adicionado (padrão: "...")
     * @return string
     */
    public static function limitText($text, $limit, $suffix = "...")
    {
        if (strlen($text) <= $limit) {
            return $text;
        }
        
        return substr($text, 0, $limit) . $suffix;
    }
    
    /**
     * Limita o número de palavras de um texto
     * 
     * @param string $text - Texto a ser limitado
     * @param int $wordLimit - Número máximo de palavras
     * @param string $suffix - Sufixo a ser adicionado (padrão: "...")
     * @return string
     */
    public static function limitWords($text, $wordLimit, $suffix = "...")
    {
        $words = explode(' ', $text);
        
        if (count($words) <= $wordLimit) {
            return $text;
        }
        
        return implode(' ', array_slice($words, 0, $wordLimit)) . $suffix;
    }
    
    /**
     * Sanitiza texto para exibição segura
     * 
     * @param string $text - Texto a ser sanitizado
     * @return string
     */
    public static function sanitize($text)
    {
        return htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
    }
    
    /**
     * Formata texto com limite de caracteres e sanitização
     * 
     * @param string $text - Texto a ser formatado
     * @param int $limit - Número máximo de caracteres
     * @param string $suffix - Sufixo a ser adicionado
     * @return string
     */
    public static function formatText($text, $limit, $suffix = "...")
    {
        $sanitized = self::sanitize($text);
        return self::limitText($sanitized, $limit, $suffix);
    }
}

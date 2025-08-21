<?php

namespace Rouangni\SmartCrud\Exceptions;

use Exception;

class SmartCrudException extends Exception
{
    /**
     * Create a new exception for stub not found
     */
    public static function stubNotFound(string $stubPath): self
    {
        return new self("Stub file not found: {$stubPath}");
    }

    /**
     * Create a new exception for invalid generator type
     */
    public static function invalidGeneratorType(string $type): self
    {
        return new self("Invalid generator type: {$type}");
    }

    /**
     * Create a new exception for invalid options
     */
    public static function invalidOptions(string $message): self
    {
        return new self("Invalid options: {$message}");
    }

    /**
     * Create a new exception for file generation failure
     */
    public static function generationFailed(string $filePath, string $reason = ''): self
    {
        $message = "Failed to generate file: {$filePath}";
        
        if ($reason) {
            $message .= " - {$reason}";
        }

        return new self($message);
    }
}
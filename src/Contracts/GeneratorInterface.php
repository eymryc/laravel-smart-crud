<?php

namespace Rouangni\SmartCrud\Contracts;

interface GeneratorInterface
{
    /**
     * Generate a file from a stub
     */
    public function generate(string $model, string $type, array $options = []): bool;

    /**
     * Check if a file already exists
     */
    public function exists(string $filePath): bool;

    /**
     * Get the path where the file will be generated
     */
    public function getFilePath(string $model, string $type, array $options = []): string;

    /**
     * Get the namespace for the generated class
     */
    public function getNamespace(string $model, string $type, array $options = []): string;

    /**
     * Get available file types that can be generated
     */
    public function getAvailableTypes(): array;
}
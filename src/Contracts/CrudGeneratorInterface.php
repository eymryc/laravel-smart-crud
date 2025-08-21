<?php

namespace Rouangni\SmartCrud\Contracts;

interface CrudGeneratorInterface extends GeneratorInterface
{
    /**
     * Generate all CRUD files for a model
     */
    public function generateAll(string $model, array $options = []): array;

    /**
     * Generate controller file
     */
    public function generateController(string $model, array $options = []): bool;

    /**
     * Generate request files
     */
    public function generateRequests(string $model, array $options = []): bool;

    /**
     * Generate routes file
     */
    public function generateRoutes(string $model, array $options = []): bool;
}
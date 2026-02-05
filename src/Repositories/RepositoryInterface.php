<?php

namespace App\Repositories;

interface RepositoryInterface
{
    /**
     * Tüm kayıtları getirir
     * 
     * @param array $filters
     * @param array $sort
     * @param int $limit
     * @param int $offset
     * @return array
     */
    public function findAll(array $filters = [], array $sort = [], int $limit = 10, int $offset = 0): array;

    /**
     * ID'ye göre kayıt getirir
     * 
     * @param int $id
     * @return array|null
     */
    public function findById(int $id): ?array;

    /**
     * Toplam kayıt sayısını getirir
     * 
     * @param array $filters
     * @return int
     */
    public function count(array $filters = []): int;
}

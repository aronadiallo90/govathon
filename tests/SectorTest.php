<?php

namespace Tests;

use PHPUnit\Framework\TestCase;

class SectorTest extends TestCase 
{
    private $sectorData;

    protected function setUp(): void
    {
        $this->sectorData = [
            'id' => 1,
            'nom' => 'Technologie',
            'description' => 'Secteur technologique',
            'created_at' => '2025-05-05 09:00:00',
            'updated_at' => '2025-05-05 09:00:00'
        ];
    }

    public function testSectorDataStructure()
    {
        $requiredFields = ['id', 'nom', 'description', 'created_at', 'updated_at'];
        foreach ($requiredFields as $field) {
            $this->assertArrayHasKey(
                $field, 
                $this->sectorData, 
                "Le champ '$field' est manquant"
            );
        }
    }

    public function testSectorDataTypes()
    {
        $this->assertIsInt(
            $this->sectorData['id'],
            "L'ID doit être un nombre entier"
        );
        $this->assertIsString(
            $this->sectorData['nom'],
            "Le nom doit être une chaîne de caractères"
        );
        $this->assertIsString(
            $this->sectorData['description'],
            "La description doit être une chaîne de caractères"
        );
        $this->assertMatchesRegularExpression(
            '/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}$/',
            $this->sectorData['created_at'],
            "La date de création doit être au format YYYY-MM-DD HH:MM:SS"
        );
        $this->assertMatchesRegularExpression(
            '/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}$/',
            $this->sectorData['updated_at'],
            "La date de mise à jour doit être au format YYYY-MM-DD HH:MM:SS"
        );
    }

    public function testSectorRequiredFields()
    {
        $this->assertNotEmpty(
            $this->sectorData['nom'],
            "Le nom ne peut pas être vide"
        );
        $this->assertNotEmpty(
            $this->sectorData['description'],
            "La description ne peut pas être vide"
        );
        $this->assertGreaterThan(
            0,
            $this->sectorData['id'],
            "L'ID doit être positif"
        );
        $this->assertTrue(
            strlen($this->sectorData['nom']) <= 255,
            "Le nom ne doit pas dépasser 255 caractères"
        );
    }

    public function testSectorFieldValidations()
    {
        // Test nom length constraints
        $this->assertLessThanOrEqual(
            255,
            strlen($this->sectorData['nom']),
            "Le nom du secteur ne doit pas dépasser 255 caractères"
        );

        // Test description length
        $this->assertLessThanOrEqual(
            1000,
            strlen($this->sectorData['description']),
            "La description ne doit pas dépasser 1000 caractères"
        );

        // Test date chronological order
        $this->assertLessThanOrEqual(
            strtotime($this->sectorData['updated_at']),
            strtotime($this->sectorData['created_at']),
            "La date de création doit être antérieure ou égale à la date de mise à jour"
        );
    }

    public function testSectorDataFormatting()
    {
        // Test nom formatting
        $this->assertMatchesRegularExpression(
            '/^[a-zA-ZÀ-ÿ\s\-\']+$/',
            $this->sectorData['nom'],
            "Le nom doit contenir uniquement des lettres, espaces, tirets et apostrophes"
        );

        // Test dates are in MySQL format
        $dateFormat = '/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}$/';
        $this->assertMatchesRegularExpression(
            $dateFormat,
            $this->sectorData['created_at'],
            "La date de création doit être au format MySQL DATETIME"
        );
    }
}
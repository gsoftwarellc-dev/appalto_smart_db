<?php

namespace App\Services;

class MockAIProvider implements AIProviderInterface
{
    /**
     * Extract BOQ data from PDF file (Mock implementation)
     *
     * @param string $filePath
     * @param string $extractionType
     * @return array
     */
    public function extractBoqFromPdf(string $filePath, string $extractionType): array
    {
        // Simulate processing delay
        sleep(2);
        
        // Mock extracted data based on extraction type
        $mockBoqItems = $this->getMockBoqData($extractionType);
        
        return [
            'success' => true,
            'data' => $mockBoqItems,
            'confidence' => 0.95,
            'error' => null,
        ];
    }
    
    /**
     * Check if provider is configured (always true for mock)
     *
     * @return bool
     */
    public function isConfigured(): bool
    {
        return true;
    }
    
    /**
     * Get mock BOQ data based on extraction type
     *
     * @param string $type
     * @return array
     */
    private function getMockBoqData(string $type): array
    {
        // Mock data simulating AI extraction results
        return [
            'tender_info' => [
                'title' => 'Extracted: Construction Project',
                'location' => 'Extracted Location',
                'estimated_budget' => 100000,
            ],
            'boq_items' => [
                [
                    'description' => 'Excavation works',
                    'unit' => 'mc',
                    'quantity' => 150.0,
                    'item_type' => 'unit_priced',
                ],
                [
                    'description' => 'Concrete foundation',
                    'unit' => 'mc',
                    'quantity' => 50.0,
                    'item_type' => 'unit_priced',
                ],
                [
                    'description' => 'Steel reinforcement',
                    'unit' => 'kg',
                    'quantity' => 2000.0,
                    'item_type' => 'unit_priced',
                ],
                [
                    'description' => 'Finishing works',
                    'unit' => 'mq',
                    'quantity' => 300.0,
                    'item_type' => 'lump_sum',
                ],
            ],
        ];
    }
}

<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PdfExtractionService
{
    protected AIProviderInterface $aiProvider;
    
    public function __construct(AIProviderInterface $aiProvider = null)
    {
        // Use mock provider if none specified or if AI is not configured
        $this->aiProvider = $aiProvider ?? new MockAIProvider();
    }
    
    /**
     * Start PDF extraction process
     *
     * @param int $documentId
     * @param int $tenderId
     * @param string $filePath
     * @param string $extractionType
     * @return int PDF extraction ID
     */
    public function startExtraction(int $documentId, int $tenderId, string $filePath, string $extractionType = 'standard'): int
    {
        // Create extraction record
        $extractionId = DB::table('pdf_extractions')->insertGetId([
            'document_id' => $documentId,
            'tender_id' => $tenderId,
            'extraction_type' => $extractionType,
            'status' => 'processing',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        
        Log::info("Started PDF extraction", [
            'extraction_id' => $extractionId,
            'document_id' => $documentId,
            'tender_id' => $tenderId,
        ]);
        
        return $extractionId;
    }
    
    /**
     * Process PDF extraction
     *
     * @param int $extractionId
     * @param string $filePath
     * @return void
     */
    public function processExtraction(int $extractionId, string $filePath): void
    {
        try {
            $extraction = DB::table('pdf_extractions')->find($extractionId);
            
            if (!$extraction) {
                throw new \Exception("Extraction record not found: {$extractionId}");
            }
            
            // Call AI provider to extract data
            $result = $this->aiProvider->extractBoqFromPdf(
                $filePath,
                $extraction->extraction_type
            );
            
            if ($result['success']) {
                // Update extraction record with success
                DB::table('pdf_extractions')
                    ->where('id', $extractionId)
                    ->update([
                        'status' => 'completed',
                        'ai_response' => json_encode($result['data']),
                        'confidence_score' => $result['confidence'],
                        'processed_at' => now(),
                        'updated_at' => now(),
                    ]);
                
                // Optionally auto-create BOQ items from extracted data
                if (isset($result['data']['boq_items'])) {
                    $this->createBoqItemsFromExtraction($extraction->tender_id, $result['data']['boq_items']);
                }
                
                Log::info("PDF extraction completed", ['extraction_id' => $extractionId]);
            } else {
                throw new \Exception($result['error'] ?? 'Extraction failed');
            }
            
        } catch (\Exception $e) {
            // Update extraction record with failure
            DB::table('pdf_extractions')
                ->where('id', $extractionId)
                ->update([
                    'status' => 'failed',
                    'error_message' => $e->getMessage(),
                    'processed_at' => now(),
                    'updated_at' => now(),
                ]);
            
            Log::error("PDF extraction failed", [
                'extraction_id' => $extractionId,
                'error' => $e->getMessage(),
            ]);
            
            throw $e;
        }
    }
    
    /**
     * Create BOQ items from extracted data
     *
     * @param int $tenderId
     * @param array $boqItems
     * @return void
     */
    protected function createBoqItemsFromExtraction(int $tenderId, array $boqItems): void
    {
        foreach ($boqItems as $index => $item) {
            DB::table('boq_items')->insert([
                'tender_id' => $tenderId,
                'description' => $item['description'],
                'unit' => $item['unit'],
                'quantity' => $item['quantity'],
                'item_type' => $item['item_type'] ?? 'unit_priced',
                'display_order' => $index + 1,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
        
        Log::info("Created BOQ items from extraction", [
            'tender_id' => $tenderId,
            'item_count' => count($boqItems),
        ]);
    }
    
    /**
     * Get extraction status
     *
     * @param int $extractionId
     * @return object|null
     */
    public function getExtractionStatus(int $extractionId): ?object
    {
        return DB::table('pdf_extractions')->find($extractionId);
    }
    
    /**
     * Get all extractions for a tender
     *
     * @param int $tenderId
     * @return array
     */
    public function getTenderExtractions(int $tenderId): array
    {
        return DB::table('pdf_extractions')
            ->where('tender_id', $tenderId)
            ->orderBy('created_at', 'desc')
            ->get()
            ->toArray();
    }
}

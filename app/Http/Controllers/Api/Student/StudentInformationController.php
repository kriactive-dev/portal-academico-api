<?php

namespace App\Http\Controllers\Api\Student;

use App\Http\Controllers\Controller;
use App\Services\Student\StudentInformationService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class StudentInformationController extends Controller
{
    protected StudentInformationService $studentInformationService;

    public function __construct(StudentInformationService $studentInformationService)
    {
        $this->studentInformationService = $studentInformationService;
    }

    public function academicInformation(int $id)
    {
        try {
            $academicInformation = $this->studentInformationService->getAcademicInformation($id);
    
            return response()->json([
                'success' => true,
                    'message' => 'Informações acadêmicas recuperadas com sucesso.',
                    'data' => $academicInformation
                ]);
            } catch (Exception $e) {
                return response()->json([
                    'success' => false,
                    'message' => 'Erro ao recuperar informações acadêmicas.',
                    'error' => $e->getMessage()
                ], 500);
            }
    }

    public function financialInformation(int $id)
    {
        try {
            $financialInformation = $this->studentInformationService->getFinancialInformation($id);
    
            return response()->json([
                'success' => true,
                    'message' => 'Informações financeiras recuperadas com sucesso.',
                    'data' => $financialInformation
                ]);
            } catch (Exception $e) {
                return response()->json([
                    'success' => false,
                    'message' => 'Erro ao recuperar informações financeiras.',
                    'error' => $e->getMessage()
                ], 500);
            }
    }

    public function personalInformation(int $id)
    {
        try {
        $personalInformation = $this->studentInformationService->getPersonalInformation($id);

        return response()->json([
            'success' => true,
                'message' => 'Informações pessoais recuperadas com sucesso.',
                'data' => $personalInformation
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao recuperar informações pessoais.',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}

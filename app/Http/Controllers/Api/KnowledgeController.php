<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Symptom;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class KnowledgeController extends Controller
{
    /**
     * Get knowledge cards for symptoms.
     */
    public function index(Request $request): JsonResponse
    {
        $symptomCodes = $request->input('symptoms', []);

        if (empty($symptomCodes)) {
            return response()->json([
                'data' => [],
                'message' => 'Vui lòng cung cấp mã triệu chứng.',
            ]);
        }

        $knowledge = $this->getKnowledgeForSymptoms($symptomCodes);

        return response()->json([
            'data' => $knowledge,
        ]);
    }

    /**
     * Get knowledge for a specific symptom.
     */
    public function show(string $symptomCode): JsonResponse
    {
        $symptom = Symptom::where('code', $symptomCode)->first();

        if (! $symptom) {
            return response()->json([
                'message' => 'Triệu chứng không tồn tại.',
            ], 404);
        }

        $knowledge = $this->getKnowledgeForSymptom($symptomCode);

        return response()->json([
            'data' => $knowledge,
        ]);
    }

    /**
     * Get knowledge cards for multiple symptoms.
     *
     * @param  array<string>  $symptomCodes
     * @return array<string, mixed>
     */
    private function getKnowledgeForSymptoms(array $symptomCodes): array
    {
        $knowledge = [];

        foreach ($symptomCodes as $code) {
            $knowledge[] = $this->getKnowledgeForSymptom($code);
        }

        return $knowledge;
    }

    /**
     * Get knowledge card for a symptom.
     *
     * @return array<string, mixed>
     */
    private function getKnowledgeForSymptom(string $symptomCode): array
    {
        $knowledgeBase = [
            'headache' => [
                'title' => 'Đau đầu',
                'content' => 'Đau đầu có thể do nhiều nguyên nhân: căng thẳng, thiếu ngủ, hoặc các vấn đề về xoang. Nếu đau đầu kéo dài hơn 3 ngày hoặc kèm theo sốt, bạn nên đi khám.',
                'severity' => 'watch',
            ],
            'fever' => [
                'title' => 'Sốt',
                'content' => 'Sốt là phản ứng tự nhiên của cơ thể khi chống lại nhiễm trùng. Sốt nhẹ (dưới 38.5°C) thường không đáng lo. Nếu sốt cao trên 39°C hoặc kéo dài, nên đi khám.',
                'severity' => 'watch',
            ],
            'cough' => [
                'title' => 'Ho',
                'content' => 'Ho có thể do cảm lạnh, dị ứng, hoặc các vấn đề về phổi. Nếu ho kéo dài hơn 7 ngày hoặc có đờm máu, bạn nên đi khám.',
                'severity' => 'watch',
            ],
            'nasal_congestion' => [
                'title' => 'Nghẹt mũi',
                'content' => 'Nghẹt mũi thường do cảm lạnh hoặc viêm xoang. Nếu kéo dài hơn 5 ngày và kèm theo đau đầu, có thể liên quan đến viêm xoang.',
                'severity' => 'watch',
            ],
            'chest_pain' => [
                'title' => 'Đau ngực',
                'content' => 'Đau ngực kèm theo khó thở có thể là dấu hiệu nghiêm trọng. Bạn nên đi khám ngay lập tức.',
                'severity' => 'critical',
            ],
            'shortness_of_breath' => [
                'title' => 'Khó thở',
                'content' => 'Khó thở có thể do nhiều nguyên nhân. Nếu kèm theo đau ngực, đây là tình trạng khẩn cấp cần được chăm sóc y tế ngay.',
                'severity' => 'critical',
            ],
            'dizziness' => [
                'title' => 'Chóng mặt',
                'content' => 'Chóng mặt có thể do thiếu nước, huyết áp thấp, hoặc các vấn đề về tai trong. Nếu bạn có bệnh nền như huyết áp cao, nên đi khám.',
                'severity' => 'watch',
            ],
        ];

        $default = [
            'title' => 'Triệu chứng',
            'content' => 'Vui lòng theo dõi triệu chứng của bạn. Nếu tình trạng không cải thiện hoặc trở nên nghiêm trọng, bạn nên đi khám.',
            'severity' => 'info',
        ];

        return $knowledgeBase[$symptomCode] ?? $default;
    }
}

<?php

namespace App\Services;

use App\Models\Insight;

class InsightExplanationService
{
    /**
     * Build a structured, user-friendly explanation for an insight.
     */
    public function buildExplanation(Insight $insight): array
    {
        $explanationData = $insight->explanation_data ?? [];
        $dataUsed = $explanationData['data_used'] ?? [];
        $ruleCode = $explanationData['rule_code'] ?? null;

        // Build story based on insight type
        $story = $this->buildStory($insight->type, $dataUsed, $ruleCode);

        // Build data summary
        $dataSummary = $this->buildDataSummary($dataUsed);

        // Get actionable insights
        $actionableInsights = $this->getActionableInsights($insight->type, $dataUsed, $insight->priority);

        return [
            'insight_summary' => [
                'message' => $insight->message,
                'type' => $insight->type,
                'priority' => $insight->priority,
                'created_at' => $insight->generated_at,
            ],
            'story' => $story,
            'data_summary' => $dataSummary,
            'actionable_insights' => $actionableInsights,
            'technical_details' => [
                'raw_data' => $dataUsed,
                'rule_code' => $ruleCode,
            ],
        ];
    }

    /**
     * Build narrative story based on insight type.
     */
    private function buildStory(string $type, array $dataUsed, ?string $ruleCode): array
    {
        return match ($type) {
            'TREND' => $this->buildTrendStory($dataUsed),
            'COMPARISON' => $this->buildComparisonStory($dataUsed),
            'PATTERN' => $this->buildPatternStory($dataUsed),
            'REASSURANCE' => $this->buildReassuranceStory($dataUsed),
            'CONTEXTUAL' => $this->buildContextualStory($dataUsed, $ruleCode),
            default => $this->buildGenericStory($dataUsed),
        };
    }

    /**
     * Build trend story.
     */
    private function buildTrendStory(array $dataUsed): array
    {
        $trend = $dataUsed['trend'] ?? [];
        $direction = $trend['direction'] ?? 'stable';
        $avg3d = $trend['3d_avg'] ?? null;
        $avg7d = $trend['7d_avg'] ?? null;
        $days = $trend['days'] ?? 0;

        $headline = 'Tại sao bạn thấy insight này?';
        $narrative = '';
        $keyFacts = [];

        if ($direction === 'worsening') {
            $narrative = "Triệu chứng của bạn đã có xu hướng nặng hơn trong {$days} ngày gần đây. ";
            if ($avg7d !== null && $avg3d !== null) {
                $diff = round($avg3d - $avg7d, 1);
                $narrative .= "Mức độ trung bình đã tăng từ {$avg7d}/10 lên {$avg3d}/10 (tăng {$diff} điểm). ";
            }
            $narrative .= 'Đây là xu hướng đáng chú ý cần theo dõi.';

            $keyFacts = [
                "Xu hướng: Nặng hơn trong {$days} ngày",
                $avg3d !== null ? "Trung bình 3 ngày: {$avg3d}/10" : null,
                $avg7d !== null ? "Trung bình 7 ngày: {$avg7d}/10" : null,
            ];
        } elseif ($direction === 'improving') {
            $narrative = "Triệu chứng của bạn đã có xu hướng cải thiện trong {$days} ngày gần đây. ";
            if ($avg7d !== null && $avg3d !== null) {
                $diff = round($avg7d - $avg3d, 1);
                $narrative .= "Mức độ trung bình đã giảm từ {$avg7d}/10 xuống {$avg3d}/10 (giảm {$diff} điểm). ";
            }
            $narrative .= 'Đây là dấu hiệu tích cực.';

            $keyFacts = [
                "Xu hướng: Cải thiện trong {$days} ngày",
                $avg3d !== null ? "Trung bình 3 ngày: {$avg3d}/10" : null,
                $avg7d !== null ? "Trung bình 7 ngày: {$avg7d}/10" : null,
            ];
        } else {
            $narrative = 'Triệu chứng của bạn đang ổn định trong những ngày gần đây. ';
            if ($avg3d !== null) {
                $narrative .= "Mức độ trung bình duy trì ở khoảng {$avg3d}/10.";
            }

            $keyFacts = [
                'Xu hướng: Ổn định',
                $avg3d !== null ? "Trung bình 3 ngày: {$avg3d}/10" : null,
            ];
        }

        $keyFacts = array_filter($keyFacts);

        return [
            'headline' => $headline,
            'narrative' => $narrative,
            'key_facts' => $keyFacts,
        ];
    }

    /**
     * Build comparison story.
     */
    private function buildComparisonStory(array $dataUsed): array
    {
        $comparison = $dataUsed['comparison'] ?? [];
        $baseline = $comparison['baseline'] ?? 'personal_average';
        $currentAvg = $comparison['current_avg'] ?? null;
        $baselineAvg = $comparison['baseline_avg'] ?? null;
        $higher = $comparison['higher'] ?? false;

        $headline = 'Tại sao bạn thấy insight này?';
        $baselineText = $this->getBaselineText($baseline);

        $narrative = '';
        $keyFacts = [];

        if ($currentAvg !== null && $baselineAvg !== null) {
            $difference = round(abs($currentAvg - $baselineAvg), 1);

            if ($higher) {
                $narrative = "Mức độ triệu chứng hiện tại của bạn ({$currentAvg}/10) cao hơn {$baselineText} ({$baselineAvg}/10) khoảng {$difference} điểm. ";
                $narrative .= 'Đây là sự thay đổi đáng chú ý so với mức ổn định trước đó.';

                $keyFacts = [
                    "Hiện tại: {$currentAvg}/10",
                    "{$baselineText}: {$baselineAvg}/10",
                    "Chênh lệch: +{$difference} điểm",
                ];
            } else {
                $narrative = "Mức độ triệu chứng hiện tại của bạn ({$currentAvg}/10) thấp hơn {$baselineText} ({$baselineAvg}/10) khoảng {$difference} điểm. ";
                $narrative .= 'Đây là dấu hiệu tích cực cho thấy tình trạng đang cải thiện.';

                $keyFacts = [
                    "Hiện tại: {$currentAvg}/10",
                    "{$baselineText}: {$baselineAvg}/10",
                    "Chênh lệch: -{$difference} điểm",
                ];
            }
        } else {
            $narrative = "Hệ thống đã so sánh mức độ triệu chứng hiện tại của bạn với {$baselineText} và phát hiện sự khác biệt đáng chú ý.";
        }

        return [
            'headline' => $headline,
            'narrative' => $narrative,
            'key_facts' => $keyFacts,
        ];
    }

    /**
     * Build pattern story.
     */
    private function buildPatternStory(array $dataUsed): array
    {
        $pattern = $dataUsed['pattern'] ?? [];
        $patternType = $pattern['pattern'] ?? 'unknown';

        $headline = 'Tại sao bạn thấy insight này?';
        $narrative = '';
        $keyFacts = [];

        if ($patternType === 'night_worsening') {
            $nightAvg = $pattern['night_avg'] ?? null;
            $dayAvg = $pattern['day_avg'] ?? null;

            $narrative = 'Hệ thống phát hiện triệu chứng của bạn nặng hơn vào ban đêm so với ban ngày. ';
            if ($nightAvg !== null && $dayAvg !== null) {
                $diff = round($nightAvg - $dayAvg, 1);
                $narrative .= "Mức độ trung bình ban đêm ({$nightAvg}/10) cao hơn ban ngày ({$dayAvg}/10) khoảng {$diff} điểm. ";
            }
            $narrative .= 'Đây là quy luật thường gặp và có thể liên quan đến nhịp sinh học hoặc môi trường.';

            $keyFacts = [
                $nightAvg !== null ? "Trung bình ban đêm: {$nightAvg}/10" : null,
                $dayAvg !== null ? "Trung bình ban ngày: {$dayAvg}/10" : null,
                'Quy luật: Nặng hơn vào ban đêm',
            ];
        } elseif ($patternType === 'weekend_worse' || $patternType === 'weekend_better') {
            $narrative = 'Hệ thống phát hiện triệu chứng của bạn có xu hướng khác biệt giữa ngày trong tuần và cuối tuần. ';
            $narrative .= 'Điều này có thể liên quan đến thói quen sinh hoạt, công việc hoặc môi trường.';

            $keyFacts = [
                'Quy luật: Khác biệt giữa ngày trong tuần và cuối tuần',
            ];
        } elseif ($patternType === 'positive_response') {
            $narrative = 'Hệ thống phát hiện triệu chứng của bạn đang cải thiện sau khi dùng thuốc. ';
            $narrative .= 'Đây là dấu hiệu tích cực cho thấy điều trị đang có hiệu quả.';

            $keyFacts = [
                'Quy luật: Cải thiện sau khi dùng thuốc',
            ];
        } elseif ($patternType === 'no_response') {
            $narrative = 'Hệ thống phát hiện triệu chứng của bạn chưa cải thiện sau khi dùng thuốc. ';
            $narrative .= 'Điều này có thể cần trao đổi với bác sĩ để điều chỉnh phương pháp điều trị.';

            $keyFacts = [
                'Quy luật: Chưa cải thiện sau khi dùng thuốc',
            ];
        } else {
            $narrative = 'Hệ thống đã phát hiện một quy luật trong dữ liệu sức khỏe của bạn.';

            $keyFacts = [
                "Quy luật: {$patternType}",
            ];
        }

        $keyFacts = array_filter($keyFacts);

        return [
            'headline' => $headline,
            'narrative' => $narrative,
            'key_facts' => $keyFacts,
        ];
    }

    /**
     * Build reassurance story.
     */
    private function buildReassuranceStory(array $dataUsed): array
    {
        $headline = 'Tại sao bạn thấy insight này?';
        $narrative = 'Tình trạng sức khỏe của bạn đang ổn định trong những ngày gần đây. ';
        $narrative .= 'Không có dấu hiệu bất thường hoặc xu hướng xấu đi. Đây là tin tốt!';

        $keyFacts = [
            'Tình trạng: Ổn định',
            'Không có cảnh báo nghiêm trọng',
        ];

        return [
            'headline' => $headline,
            'narrative' => $narrative,
            'key_facts' => $keyFacts,
        ];
    }

    /**
     * Build contextual story.
     */
    private function buildContextualStory(array $dataUsed, ?string $ruleCode): array
    {
        $headline = 'Tại sao bạn thấy insight này?';
        $narrative = 'Hệ thống đã phân tích dữ liệu sức khỏe của bạn trong bối cảnh cụ thể và phát hiện điều đáng chú ý. ';
        $narrative .= 'Insight này được tạo dựa trên các quy luật y tế và dữ liệu cá nhân của bạn.';

        $keyFacts = [
            'Loại: Insight theo ngữ cảnh',
        ];

        return [
            'headline' => $headline,
            'narrative' => $narrative,
            'key_facts' => $keyFacts,
        ];
    }

    /**
     * Build generic story fallback.
     */
    private function buildGenericStory(array $dataUsed): array
    {
        $headline = 'Tại sao bạn thấy insight này?';
        $narrative = 'Hệ thống đã phân tích dữ liệu sức khỏe của bạn và phát hiện điều đáng chú ý. ';
        $narrative .= 'Insight này được tạo dựa trên các quy luật và dữ liệu bạn đã ghi nhận.';

        return [
            'headline' => $headline,
            'narrative' => $narrative,
            'key_facts' => [],
        ];
    }

    /**
     * Build data summary.
     */
    private function buildDataSummary(array $dataUsed): array
    {
        $summary = [
            'time_period' => '7 ngày gần đây',
            'main_comparison' => null,
            'supporting_data' => [],
        ];

        // Extract time period from trend data
        if (isset($dataUsed['trend'])) {
            $days = $dataUsed['trend']['days'] ?? 0;
            if ($days > 0) {
                $summary['time_period'] = "{$days} ngày gần đây";
            }
        }

        // Build main comparison
        if (isset($dataUsed['comparison'])) {
            $comparison = $dataUsed['comparison'];
            $current = $comparison['current_avg'] ?? null;
            $baseline = $comparison['baseline_avg'] ?? null;
            $baselineType = $this->getBaselineText($comparison['baseline'] ?? 'personal_average');

            if ($current !== null && $baseline !== null) {
                $diff = round(abs($current - $baseline), 1);
                $direction = ($current > $baseline) ? 'cao hơn' : 'thấp hơn';
                $summary['main_comparison'] = "{$direction} {$diff} điểm so với {$baselineType}";
            }
        }

        // Add supporting data
        if (isset($dataUsed['trend'])) {
            $trend = $dataUsed['trend'];
            if (isset($trend['3d_avg'])) {
                $summary['supporting_data'][] = [
                    'label' => 'Trung bình 3 ngày',
                    'value' => $trend['3d_avg'].'/10',
                ];
            }
            if (isset($trend['7d_avg'])) {
                $summary['supporting_data'][] = [
                    'label' => 'Trung bình 7 ngày',
                    'value' => $trend['7d_avg'].'/10',
                ];
            }
        }

        return $summary;
    }

    /**
     * Get actionable insights based on insight type and priority.
     */
    private function getActionableInsights(string $type, array $dataUsed, string $priority): array
    {
        $insights = [];

        if ($type === 'TREND') {
            $direction = $dataUsed['trend']['direction'] ?? 'stable';
            if ($direction === 'worsening') {
                $insights = [
                    'Theo dõi sát diễn biến trong những ngày tới',
                    'Ghi nhận các yếu tố có thể ảnh hưởng (thức ăn, môi trường, thuốc)',
                    $priority === 'high' ? 'Trao đổi với bác sĩ nếu triệu chứng tiếp tục nặng hơn' : null,
                ];
            } elseif ($direction === 'improving') {
                $insights = [
                    'Tiếp tục duy trì các thói quen hiện tại',
                    'Ghi nhận các yếu tố tích cực đang giúp cải thiện',
                ];
            } else {
                $insights = [
                    'Duy trì thói quen hiện tại',
                    'Tiếp tục theo dõi định kỳ',
                ];
            }
        } elseif ($type === 'COMPARISON') {
            $higher = $dataUsed['comparison']['higher'] ?? false;
            if ($higher) {
                $insights = [
                    'Xem xét các thay đổi gần đây trong sinh hoạt',
                    'Theo dõi xem triệu chứng có tiếp tục tăng không',
                    $priority === 'medium' ? 'Cân nhắc trao đổi với bác sĩ nếu triệu chứng kéo dài' : null,
                ];
            } else {
                $insights = [
                    'Tiếp tục duy trì các thói quen tích cực',
                    'Ghi nhận các yếu tố đang giúp cải thiện',
                ];
            }
        } elseif ($type === 'PATTERN') {
            $patternType = $dataUsed['pattern']['pattern'] ?? '';
            if ($patternType === 'night_worsening') {
                $insights = [
                    'Chú ý đến môi trường phòng ngủ (độ ẩm, nhiệt độ)',
                    'Cân nhắc điều chỉnh thời gian dùng thuốc nếu phù hợp',
                ];
            } elseif ($patternType === 'no_response') {
                $insights = [
                    'Trao đổi với bác sĩ về hiệu quả điều trị',
                    'Ghi nhận đầy đủ các triệu chứng để bác sĩ đánh giá',
                ];
            }
        } elseif ($type === 'REASSURANCE') {
            $insights = [
                'Tiếp tục duy trì thói quen hiện tại',
                'Tiếp tục theo dõi định kỳ',
            ];
        }

        return array_filter($insights);
    }

    /**
     * Get baseline text in Vietnamese.
     */
    private function getBaselineText(string $baseline): string
    {
        return match ($baseline) {
            'last_week' => 'tuần trước',
            'personal_average' => 'trung bình cá nhân',
            default => $baseline,
        };
    }
}

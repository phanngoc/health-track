<?php

return [
    'rules' => [
        // ============================================
        // TREND Insights
        // ============================================
        'INS_TREND_WORSENING' => [
            'type' => 'TREND',
            'when' => [
                'trend' => 'worsening',
                'duration_days' => 3,
            ],
            'then' => [
                'priority' => 'high',
                'message' => 'Triệu chứng của bạn đang có xu hướng nặng hơn trong vài ngày gần đây. Hãy theo dõi sát và cân nhắc trao đổi với bác sĩ nếu tiếp diễn.',
            ],
        ],
        'INS_TREND_IMPROVING' => [
            'type' => 'TREND',
            'when' => [
                'trend' => 'improving',
                'duration_days' => 3,
            ],
            'then' => [
                'priority' => 'low',
                'message' => 'Triệu chứng của bạn đang có xu hướng cải thiện trong vài ngày gần đây.',
            ],
        ],
        'INS_STABLE_REASSURE' => [
            'type' => 'REASSURANCE',
            'when' => [
                'trend' => 'stable',
                'no_critical_alerts' => true,
            ],
            'then' => [
                'priority' => 'low',
                'message' => 'Tình trạng sức khỏe của bạn đang ổn định trong những ngày gần đây.',
            ],
        ],

        // ============================================
        // PATTERN Insights
        // ============================================
        'INS_PATTERN_NIGHT_WORSENING' => [
            'type' => 'PATTERN',
            'when' => [
                'pattern' => 'night_worsening',
            ],
            'then' => [
                'priority' => 'medium',
                'message' => 'Triệu chứng của bạn nặng hơn rõ rệt vào ban đêm.',
            ],
        ],
        'INS_PATTERN_WEEKEND_DIFFERENCE' => [
            'type' => 'PATTERN',
            'when' => [
                'pattern' => 'weekend_worse',
            ],
            'then' => [
                'priority' => 'low',
                'message' => 'Triệu chứng của bạn có xu hướng khác biệt giữa ngày trong tuần và cuối tuần.',
            ],
        ],
        'INS_PATTERN_POSITIVE_RESPONSE' => [
            'type' => 'PATTERN',
            'when' => [
                'pattern' => 'positive_response',
            ],
            'then' => [
                'priority' => 'low',
                'message' => 'Triệu chứng của bạn đang cải thiện sau khi dùng thuốc.',
            ],
        ],
        'INS_PATTERN_NO_RESPONSE' => [
            'type' => 'PATTERN',
            'when' => [
                'pattern' => 'no_response',
            ],
            'then' => [
                'priority' => 'medium',
                'message' => 'Triệu chứng của bạn chưa cải thiện sau khi dùng thuốc. Hãy trao đổi với bác sĩ.',
            ],
        ],

        // ============================================
        // COMPARISON Insights
        // ============================================
        'INS_COMPARISON_HIGHER_THAN_AVERAGE' => [
            'type' => 'COMPARISON',
            'when' => [
                'baseline' => ['last_week', 'personal_average'],
                'higher' => true,
            ],
            'then' => [
                'priority' => 'medium',
                'message' => 'Mức độ triệu chứng hiện tại cao hơn trung bình của bạn.',
            ],
        ],
        'INS_COMPARISON_LOWER_THAN_AVERAGE' => [
            'type' => 'COMPARISON',
            'when' => [
                'baseline' => ['last_week', 'personal_average'],
                'higher' => false,
            ],
            'then' => [
                'priority' => 'low',
                'message' => 'Mức độ triệu chứng hiện tại thấp hơn trung bình của bạn.',
            ],
        ],

        // ============================================
        // Disease-specific Insights
        // ============================================
        'INS_AD_SLEEP_IMPACT' => [
            'type' => 'CONTEXTUAL',
            'when' => [
                'disease' => 'AD',
                'itch' => 6,
                'sleep_disturbance' => 5,
            ],
            'then' => [
                'priority' => 'medium',
                'message' => 'Ngứa da đang ảnh hưởng đến giấc ngủ, đây là dấu hiệu viêm da cơ địa có xu hướng bùng phát.',
            ],
        ],
        'INS_AR_NIGHT_WORSENING' => [
            'type' => 'CONTEXTUAL',
            'when' => [
                'disease' => 'AR',
                'pattern' => 'night_worsening',
            ],
            'then' => [
                'priority' => 'medium',
                'message' => 'Triệu chứng viêm mũi dị ứng của bạn nặng hơn vào ban đêm, đây là điều thường gặp.',
            ],
        ],
    ],
];

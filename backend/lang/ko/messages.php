<?php

return [
    'auth' => [
        'authenticated' => '인증되었습니다',
        'invalid_credentials' => '입력한 인증 정보가 올바르지 않습니다.',
    ],
    'payment' => [
        'forbidden' => '이 작업을 수행할 권한이 없습니다.',
        'not_found' => '결제를 찾을 수 없습니다.',
        'not_pending' => '대기 중인 결제만 승인하거나 거절할 수 있습니다.',
        'rate_unavailable' => '환율을 일시적으로 사용할 수 없습니다. 나중에 다시 시도해 주세요.',
        'unsupported_currency' => '귀하의 통화는 환율 변환을 지원하지 않습니다.',
    ],
    'employee' => [
        'forbidden' => '재무 팀만 직원 계정을 관리할 수 있습니다.',
        'country_not_supported' => '선택한 국가는 지원되지 않습니다.',
        'validation' => [
            'name_required' => '직원 이름은 필수입니다.',
            'email_required' => '이메일은 필수입니다.',
            'email_invalid' => '유효한 이메일 주소를 입력하세요.',
            'email_taken' => '이 이메일로 등록된 계정이 이미 있습니다.',
            'password_required' => '비밀번호는 필수입니다.',
            'password_min' => '비밀번호는 최소 6자 이상이어야 합니다.',
            'country_required' => '국가는 필수입니다.',
        ],
    ],
];

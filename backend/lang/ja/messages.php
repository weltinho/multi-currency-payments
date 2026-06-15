<?php

return [
    'auth' => [
        'authenticated' => '認証されました',
        'invalid_credentials' => '入力された認証情報が正しくありません。',
        'password_change_required' => '続行する前にパスワードを変更してください。',
        'password_changed' => 'パスワードを更新しました。',
        'validation' => [
            'current_password_required' => '現在のパスワードは必須です。',
            'current_password_invalid' => '現在のパスワードが正しくありません。',
            'password_required' => '新しいパスワードは必須です。',
            'password_format' => '新しいパスワードは6桁の数字である必要があります。',
            'password_confirmed' => 'パスワードの確認が一致しません。',
        ],
    ],
    'payment' => [
        'forbidden' => 'この操作を実行する権限がありません。',
        'not_found' => '支払いが見つかりません。',
        'not_pending' => '保留中の支払いのみ承認または却下できます。',
        'rate_unavailable' => '為替レートは一時的に利用できません。しばらくしてから再度お試しください。',
        'rate_unavailable_missing_key' => '為替レートを利用できません: backend/.env に EXCHANGE_RATE_API_KEY を設定してください（exchangerate-api.com で無料キー）。',
        'unsupported_currency' => 'お使いの通貨は為替レート変換に対応していません。',
    ],
    'employee' => [
        'forbidden' => '従業員アカウントを管理できるのは財務チームのみです。',
        'country_not_supported' => '選択した国はサポートされていません。',
        'validation' => [
            'name_required' => '従業員名は必須です。',
            'email_required' => 'メールアドレスは必須です。',
            'email_invalid' => '有効なメールアドレスを入力してください。',
            'email_taken' => 'このメールアドレスのアカウントは既に存在します。',
            'password_required' => 'パスワードは必須です。',
            'password_min' => 'パスワードは6文字以上である必要があります。',
            'country_required' => '国は必須です。',
        ],
    ],
];

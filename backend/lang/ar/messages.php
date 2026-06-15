<?php

return [
    'auth' => [
        'authenticated' => 'تمت المصادقة',
        'invalid_credentials' => 'بيانات الاعتماد المقدمة غير صحيحة.',
        'password_change_required' => 'يجب تغيير كلمة المرور قبل المتابعة.',
        'password_changed' => 'تم تحديث كلمة المرور بنجاح.',
        'validation' => [
            'current_password_required' => 'كلمة المرور الحالية مطلوبة.',
            'current_password_invalid' => 'كلمة المرور الحالية غير صحيحة.',
            'password_required' => 'كلمة المرور الجديدة مطلوبة.',
            'password_format' => 'يجب أن تتكون كلمة المرور الجديدة من 6 أرقام بالضبط.',
            'password_confirmed' => 'تأكيد كلمة المرور غير متطابق.',
        ],
    ],
    'payment' => [
        'forbidden' => 'ليس لديك إذن لتنفيذ هذا الإجراء.',
        'not_found' => 'لم يتم العثور على الدفع.',
        'not_pending' => 'يمكن اعتماد أو رفض المدفوعات المعلقة فقط.',
        'rate_unavailable' => 'سعر الصرف غير متاح مؤقتًا. يرجى المحاولة مرة أخرى لاحقًا.',
        'rate_unavailable_missing_key' => 'سعر الصرف غير متاح: عيّن EXCHANGE_RATE_API_KEY في backend/.env (مفتاح مجاني على exchangerate-api.com).',
        'unsupported_currency' => 'عملتك غير مدعومة لتحويل سعر الصرف.',
    ],
    'employee' => [
        'forbidden' => 'يمكن لفريق المالية فقط إدارة حسابات الموظفين.',
        'country_not_supported' => 'البلد المحدد غير مدعوم.',
        'validation' => [
            'name_required' => 'اسم الموظف مطلوب.',
            'email_required' => 'البريد الإلكتروني مطلوب.',
            'email_invalid' => 'أدخل عنوان بريد إلكتروني صالح.',
            'email_taken' => 'يوجد حساب بهذا البريد الإلكتروني بالفعل.',
            'password_required' => 'كلمة المرور مطلوبة.',
            'password_min' => 'يجب أن تتكون كلمة المرور من 6 أحرف على الأقل.',
            'country_required' => 'البلد مطلوب.',
        ],
    ],
];

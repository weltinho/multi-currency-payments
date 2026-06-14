<?php

return [
    'auth' => [
        'authenticated' => 'Autenticado',
        'invalid_credentials' => 'As credenciais fornecidas estão incorretas.',
    ],
    'payment' => [
        'forbidden' => 'Não tem permissão para executar esta ação.',
        'not_found' => 'Pagamento não encontrado.',
        'not_pending' => 'Apenas pagamentos pendentes podem ser aprovados ou rejeitados.',
        'rate_unavailable' => 'A taxa de câmbio está temporariamente indisponível. Tente novamente mais tarde.',
        'unsupported_currency' => 'A sua moeda não é suportada para conversão de taxa de câmbio.',
    ],
    'employee' => [
        'forbidden' => 'Apenas a equipa financeira pode gerir contas de funcionários.',
        'country_not_supported' => 'O país selecionado não é suportado.',
        'validation' => [
            'name_required' => 'O nome do funcionário é obrigatório.',
            'email_required' => 'O email é obrigatório.',
            'email_invalid' => 'Introduza um endereço de email válido.',
            'email_taken' => 'Já existe uma conta com este email.',
            'password_required' => 'A palavra-passe é obrigatória.',
            'password_min' => 'A palavra-passe deve ter pelo menos 6 caracteres.',
            'country_required' => 'O país é obrigatório.',
        ],
    ],
];

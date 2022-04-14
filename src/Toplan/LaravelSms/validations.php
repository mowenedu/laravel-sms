<?php

Validator::extend('zh_mobile', function ($attribute, $value) {
    return preg_match('/^(\+?0?86\-?)?((13\d|14[56789]|15[^4,\D]|16[2567]|17[01235678]|18\d|19[^4,\D])\d{8}|(1400\d|1410\d|1440\d|1740\d|1741[012])\d{6})$/', $value);
});

Validator::extend('confirm_mobile_not_change', function ($attribute, $value) {
    $state = SmsManager::retrieveState();

    return $state && $state['to'] === $value;
});

Validator::extend('verify_code', function ($attribute, $value) {
    $state = SmsManager::retrieveState();
    if (isset($state['attempts'])) {
        $maxAttempts = config('laravel-sms.verifyCode.maxAttempts',
            config('laravel-sms.code.maxAttempts', 0));
        $attempts = $state['attempts'] + 1;
        SmsManager::updateState('attempts', $attempts);
        if ($maxAttempts > 0 && $attempts > $maxAttempts) {
            return false;
        }
    }

    return $state && $state['deadline'] >= time() && $state['code'] === $value;
});

Validator::extend('confirm_rule', function ($attribute, $value, $parameters) {
    $state = SmsManager::retrieveState();
    $name = null;
    if (array_key_exists(0, $parameters)) {
        $name = $parameters[0];
    } elseif ($path = SmsManager::pathOfUrl(URL::previous())) {
        $name = $path;
    }

    return $state && array_key_exists($attribute, $state['usedRule']) && $state['usedRule'][$attribute] === $name;
});

<?php

declare(strict_types=1);

return [

    /*
    |--------------------------------------------------------------------------
    | Validation Language Lines
    |--------------------------------------------------------------------------
    |
    | The following language lines contain the default error messages used by
    | the validator class. Some of these rules have multiple versions such
    | as the size rules. Feel free to tweak each of these messages here.
    |
    */

    'accepted'        => 'فیلد :attribute باید پذیرفته شود.',
    'accepted_if'     => 'فیلد :attribute باید زمانی که :other برابر با :value است، پذیرفته شود.',
    'active_url'      => 'فیلد :attribute باید یک URL معتبر باشد.',
    'after'           => 'فیلد :attribute باید تاریخی بعد از :date باشد.',
    'after_or_equal'  => 'فیلد :attribute باید تاریخی بعد از یا برابر با :date باشد.',
    'alpha'           => 'فیلد :attribute فقط باید شامل حروف باشد.',
    'alpha_dash'      => 'فیلد :attribute فقط باید شامل حروف، اعداد، خط تیره و زیرخط باشد.',
    'alpha_num'       => 'فیلد :attribute فقط باید شامل حروف و اعداد باشد.',
    'array'           => 'فیلد :attribute باید یک آرایه باشد.',
    'ascii'           => 'فیلد :attribute فقط باید شامل کاراکترهای الفبایی و نمادهای تک‌بایت باشد.',
    'before'          => 'فیلد :attribute باید تاریخی قبل از :date باشد.',
    'before_or_equal' => 'فیلد :attribute باید تاریخی قبل از یا برابر با :date باشد.',
    'between'         => [
        'array'   => 'فیلد :attribute باید بین :min و :max آیتم داشته باشد.',
        'file'    => 'فیلد :attribute باید بین :min و :max کیلوبایت باشد.',
        'numeric' => 'فیلد :attribute باید بین :min و :max باشد.',
        'string'  => 'فیلد :attribute باید بین :min و :max کاراکتر باشد.',
    ],
    'boolean'           => 'فیلد :attribute باید صحیح یا غلط باشد.',
    'can'               => 'فیلد :attribute شامل مقداری غیرمجاز است.',
    'confirmed'         => 'تأیید فیلد :attribute مطابقت ندارد.',
    'contains'          => 'The :attribute field is missing a required value.',
    'current_password'  => 'کلمه عبور نادرست است.',
    'date'              => 'فیلد :attribute باید یک تاریخ معتبر باشد.',
    'date_equals'       => 'فیلد :attribute باید تاریخی برابر با :date باشد.',
    'date_format'       => 'فیلد :attribute باید با فرمت :format مطابقت داشته باشد.',
    'decimal'           => 'فیلد :attribute باید دارای :decimal مکان اعشار باشد.',
    'declined'          => 'فیلد :attribute باید رد شود.',
    'declined_if'       => 'فیلد :attribute باید زمانی که :other برابر با :value است، رد شود.',
    'different'         => 'فیلد :attribute و :other باید متفاوت باشند.',
    'digits'            => 'فیلد :attribute باید :digits رقم باشد.',
    'digits_between'    => 'فیلد :attribute باید بین :min و :max رقم باشد.',
    'dimensions'        => 'فیلد :attribute دارای ابعاد تصویر نامعتبر است.',
    'distinct'          => 'فیلد :attribute دارای مقدار تکراری است.',
    'doesnt_end_with'   => 'فیلد :attribute نباید با یکی از موارد زیر به پایان برسد: :values.',
    'doesnt_start_with' => 'فیلد :attribute نباید با یکی از موارد زیر شروع شود: :values.',
    'email'             => 'فیلد :attribute باید یک آدرس ایمیل معتبر باشد.',
    'ends_with'         => 'فیلد :attribute باید با یکی از موارد زیر به پایان برسد: :values.',
    'enum'              => 'مقدار انتخاب شده برای :attribute نامعتبر است.',
    'exists'            => 'مقدار انتخاب شده برای :attribute نامعتبر است.',
    'extensions'        => 'فیلد :attribute باید یکی از پسوندهای زیر را داشته باشد: :values.',
    'file'              => 'فیلد :attribute باید یک فایل باشد.',
    'filled'            => 'فیلد :attribute باید دارای مقداری باشد.',
    'gt'                => [
        'array'   => 'فیلد :attribute باید بیشتر از :value آیتم داشته باشد.',
        'file'    => 'فیلد :attribute باید بیشتر از :value کیلوبایت باشد.',
        'numeric' => 'فیلد :attribute باید بیشتر از :value باشد.',
        'string'  => 'فیلد :attribute باید بیشتر از :value کاراکتر باشد.',
    ],
    'gte' => [
        'array'   => 'فیلد :attribute باید دارای :value آیتم یا بیشتر باشد.',
        'file'    => 'فیلد :attribute باید بیشتر از یا برابر با :value کیلوبایت باشد.',
        'numeric' => 'فیلد :attribute باید بیشتر از یا برابر با :value باشد.',
        'string'  => 'فیلد :attribute باید بیشتر از یا برابر با :value کاراکتر باشد.',
    ],
    'hex_color' => 'فیلد :attribute باید یک رنگ هگزادسیمال معتبر باشد.',
    'image'     => 'فیلد :attribute باید یک تصویر باشد.',
    'in'        => 'مقدار انتخاب شده برای :attribute نامعتبر است.',
    'in_array'  => 'فیلد :attribute باید در :other وجود داشته باشد.',
    'integer'   => 'فیلد :attribute باید یک عدد صحیح باشد.',
    'ip'        => 'فیلد :attribute باید یک آدرس IP معتبر باشد.',
    'ipv4'      => 'فیلد :attribute باید یک آدرس IPv4 معتبر باشد.',
    'ipv6'      => 'فیلد :attribute باید یک آدرس IPv6 معتبر باشد.',
    'json'      => 'فیلد :attribute باید یک رشته JSON معتبر باشد.',
    'list'      => 'The :attribute field must be a list.',
    'lowercase' => 'فیلد :attribute باید با حروف کوچک باشد.',
    'lt'        => [
        'array'   => 'فیلد :attribute باید کمتر از :value آیتم داشته باشد.',
        'file'    => 'فیلد :attribute باید کمتر از :value کیلوبایت باشد.',
        'numeric' => 'فیلد :attribute باید کمتر از :value باشد.',
        'string'  => 'فیلد :attribute باید کمتر از :value کاراکتر باشد.',
    ],
    'lte' => [
        'array'   => 'فیلد :attribute نباید بیشتر از :value آیتم داشته باشد.',
        'file'    => 'فیلد :attribute باید کمتر از یا برابر با :value کیلوبایت باشد.',
        'numeric' => 'فیلد :attribute باید کمتر از یا برابر با :value باشد.',
        'string'  => 'فیلد :attribute باید کمتر از یا برابر با :value کاراکتر باشد.',
    ],
    'mac_address' => 'فیلد :attribute باید یک آدرس MAC معتبر باشد.',
    'max'         => [
        'array'   => 'فیلد :attribute نباید بیشتر از :max آیتم داشته باشد.',
        'file'    => 'فیلد :attribute نباید بیشتر از :max کیلوبایت باشد.',
        'numeric' => 'فیلد :attribute نباید بیشتر از :max باشد.',
        'string'  => 'فیلد :attribute نباید بیشتر از :max کاراکتر باشد.',
    ],
    'max_digits' => 'فیلد :attribute نباید بیشتر از :max رقم داشته باشد.',
    'mimes'      => 'فیلد :attribute باید از نوع فایل: :values باشد.',
    'mimetypes'  => 'فیلد :attribute باید از نوع فایل: :values باشد.',
    'min'        => [
        'array'   => 'فیلد :attribute باید حداقل :min آیتم داشته باشد.',
        'file'    => 'فیلد :attribute باید حداقل :min کیلوبایت باشد.',
        'numeric' => 'فیلد :attribute باید حداقل :min باشد.',
        'string'  => 'فیلد :attribute باید حداقل :min کاراکتر باشد.',
    ],
    'min_digits'       => 'فیلد :attribute باید حداقل :min رقم داشته باشد.',
    'missing'          => 'فیلد :attribute باید مفقود باشد.',
    'missing_if'       => 'فیلد :attribute باید مفقود باشد زمانی که :other برابر با :value است.',
    'missing_unless'   => 'فیلد :attribute باید مفقود باشد مگر اینکه :other در :values باشد.',
    'missing_with'     => 'فیلد :attribute باید مفقود باشد زمانی که :values موجود است.',
    'missing_with_all' => 'فیلد :attribute باید مفقود باشد زمانی که :values موجود هستند.',
    'multiple_of'      => 'فیلد :attribute باید مضربی از :value باشد.',
    'not_in'           => 'مقدار انتخاب شده برای :attribute نامعتبر است.',
    'not_regex'        => 'فرمت فیلد :attribute نامعتبر است.',
    'numeric'          => 'فیلد :attribute باید یک عدد باشد.',
    'password'         => [
        'letters'       => 'فیلد :attribute باید حداقل شامل یک حرف باشد.',
        'mixed'         => 'فیلد :attribute باید حداقل شامل یک حرف بزرگ و یک حرف کوچک باشد.',
        'numbers'       => 'فیلد :attribute باید حداقل شامل یک عدد باشد.',
        'symbols'       => 'فیلد :attribute باید حداقل شامل یک نماد باشد.',
        'uncompromised' => 'فیلد :attribute در یک نشت داده ظاهر شده است. لطفاً :attribute دیگری انتخاب کنید.',
    ],
    'present'                => 'فیلد :attribute باید موجود باشد.',
    'present_if'             => 'The :attribute field must be present when :other is :value.',
    'present_unless'         => 'The :attribute field must be present unless :other is :value.',
    'present_with'           => 'The :attribute field must be present when :values is present.',
    'present_with_all'       => 'The :attribute field must be present when :values are present.',
    'prohibited'             => 'فیلد :attribute ممنوع است.',
    'prohibited_if'          => 'فیلد :attribute ممنوع است زمانی که :other برابر با :value است.',
    'prohibited_if_accepted' => 'The :attribute field is prohibited when :other is accepted.',
    'prohibited_if_declined' => 'The :attribute field is prohibited when :other is declined.',
    'prohibited_unless'      => 'فیلد :attribute ممنوع است مگر اینکه :other در :values باشد.',
    'prohibits'              => 'فیلد :attribute باید موجود بودن :other را ممنوع کند.',
    'regex'                  => 'فرمت فیلد :attribute نامعتبر است.',
    'required'               => 'فیلد :attribute ضروری است.',
    'required_array_keys'    => 'فیلد :attribute باید شامل کلیدهایی برای: :values باشد.',
    'required_if'            => 'فیلد :attribute زمانی که :other برابر با :value است، ضروری است.',
    'required_if_accepted'   => 'فیلد :attribute زمانی که :other پذیرفته شده است، ضروری است.',
    'required_if_declined'   => 'The :attribute field is required when :other is declined.',
    'required_unless'        => 'فیلد :attribute ضروری است مگر اینکه :other در :values باشد.',
    'required_with'          => 'فیلد :attribute زمانی که :values موجود است، ضروری است.',
    'required_with_all'      => 'فیلد :attribute زمانی که :values موجود است، ضروری است.',
    'required_without'       => 'فیلد :attribute زمانی که :values موجود نیست، ضروری است.',
    'required_without_all'   => 'فیلد :attribute زمانی که هیچ یک از :values موجود نیستند، ضروری است.',
    'same'                   => 'فیلد :attribute و :other باید مطابقت داشته باشند.',
    'size'                   => [
        'array'   => 'فیلد :attribute باید شامل :size آیتم باشد.',
        'file'    => 'فیلد :attribute باید :size کیلوبایت باشد.',
        'numeric' => 'فیلد :attribute باید :size باشد.',
        'string'  => 'فیلد :attribute باید :size کاراکتر باشد.',
    ],
    'starts_with' => 'فیلد :attribute باید با یکی از موارد زیر شروع شود: :values.',
    'string'      => 'فیلد :attribute باید یک رشته باشد.',
    'timezone'    => 'فیلد :attribute باید یک منطقه زمانی معتبر باشد.',
    'unique'      => 'فیلد :attribute قبلاً گرفته شده است.',
    'uploaded'    => 'فیلد :attribute نتوانسته است بارگذاری شود.',
    'uppercase'   => 'The :attribute field must be uppercase.',
    'url'         => 'فرمت فیلد :attribute نامعتبر است.',
    'ulid'        => 'The :attribute field must be a valid ULID.',
    'uuid'        => 'فیلد :attribute باید یک UUID معتبر باشد.',

    /*
    |--------------------------------------------------------------------------
    | Custom Validation Language Lines
    |--------------------------------------------------------------------------
    |
    | Here you may specify custom validation messages for attributes using the
    | convention "attribute.rule" to name the lines. This makes it quick to
    | specify a specific custom language line for a given attribute rule.
    |
    */

    'custom' => [
        'attribute-name' => [
            'rule-name' => 'custom-message',
        ],

        'email' => [
            'domain_not_allowed'  => 'دامنه :domain مجاز نیست. لطفاً با یک نام دامنه دیگر از لیست دامنه‌های مجاز امتحان کنید: :allowed_domains',
            'service_unavailable' => 'متاسفانه در حال حاضر قادر به بررسی :attribute نیستیم. لطفاً بعداً دوباره تلاش کنید.',
            'server_error'        => 'یک مشکل در سرور هنگام بررسی :attribute پیش آمده است. لطفاً بعداً دوباره تلاش کنید.',
            'undeliverable'       => ':attribute معتبر نیست و نمی‌توان آن را ارسال کرد. لطفاً یک آدرس صحیح وارد کنید.',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Custom Validation Attributes
    |--------------------------------------------------------------------------
    |
    | The following language lines are used to swap our attribute placeholder
    | with something more reader friendly such as "E-Mail Address" instead
    | of "email". This simply helps us make our message more expressive.
    |
    */

    'attributes' => [],

];

<?php

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

    'valid_grilla' => 'El campo :attribute contiene errores.',
    'is_natural_no_zero' => 'El campo :attribute no es un número natural excluyendo el cero.',
    'rut' => 'El campo :attribute no es un RUN válido',
    'valid_email' => 'El campo :attribute no es un email válido',
    'emails' => 'Los correos electrónicos indicados en el campo no son correos electrónicos válidos.',
    'captcha' => 'Captcha invalido.',
    'approved' => 'Esta cuenta aún no ha sido aceptada por la CNE',
    'available_role' => 'No tienes permisos para usar este Rol',
    'accepted' => 'El campo :attribute debe ser aceptado.',
    'active_url' => 'El campo :attribute no es una URL válida.',
    'after' => 'El campo :attribute debe ser una fecha posterior a :date.',
    'after_or_equal' => 'El campo :attribute debe ser una fecha posterior o igual a :date.',
    'alpha' => 'El campo :attribute sólo puede contener letras.',
    'alpha_dash' => 'El campo :attribute sólo puede contener letras, números y guiones (a-z, 0-9, -_).',
    'alpha_num' => 'El campo :attribute sólo puede contener letras y números.',
    'array' => 'El campo :attribute debe ser un array.',
    'before' => 'El campo :attribute debe ser una fecha anterior a :date.',
    'before_or_equal' => 'El campo :attribute debe ser una fecha anterior o igual a :date.',
    'between' => [
        'numeric' => 'El campo :attribute debe ser un valor entre :min y :max.',
        'file' => 'El archivo :attribute debe pesar entre :min y :max kilobytes.',
        'string' => ':attribute : Verificar si cumple con un mínimo de :min opciones y máximo de  :max opciones requeridas.', //JP
        'array' => 'El campo :attribute debe contener entre :min y :max elementos.', 
    ],
    'boolean' => 'El campo :attribute debe ser verdadero o falso.',
    'confirmed' => 'El campo confirmación no coincide.',
    'country' => 'El campo :attribute no es un país válido.',
    'date' => 'El campo :attribute no corresponde con una fecha válida.',
    'date_format' => 'El campo :attribute no corresponde con el formato de fecha :format.',
    'different' => 'Los campos :attribute y :other han de ser diferentes.',
    'digits' => 'El campo :attribute debe ser un número de :digits dígitos.',
    'digits_between' => 'El campo :attribute debe contener entre :min y :max dígitos.',
    'dimensions' => 'El campo :attribute tiene dimensiones invalidas.',
    'distinct' => 'El campo :attribute tiene un valor duplicado.',
    'email' => 'El campo :attribute no corresponde con una dirección de e-mail válida.',
    'file' => 'El campo :attribute debe ser un archivo.',
    'filled' => 'El campo :attribute es obligatorio.',
    'exists' => 'El campo :attribute no existe.',
    'image' => 'El campo :attribute debe ser una imagen.',
    'in' => 'El campo :attribute debe ser igual a alguno de estos valores :values',
    'in_array' => 'El campo :attribute no existe en :other.',
    'integer' => 'El campo :attribute debe ser un número entero.',
    'ip' => 'El campo :attribute debe ser una dirección IP válida.',
    'json' => 'El campo :attribute debe ser una cadena de texto JSON válida.',
    'max' => [
        'numeric' => 'El campo :attribute debe ser :max como máximo.',
        'file' => 'El archivo :attribute debe pesar :max kilobytes como máximo.',
        'string' => 'El campo :attribute debe contener :max caracteres como máximo.',
        'array' => 'El campo :attribute debe contener :max elementos como máximo.',
    ],
    'mimes' => 'El campo :attribute debe ser un archivo de tipo :values.',
    'mimetypes' => 'El campo :attribute debe ser un archivo de tipo :values.',
    'min' => [
        'numeric' => 'El campo :attribute debe tener al menos :min.',
        'file' => 'El archivo :attribute debe pesar al menos :min kilobytes.',
        'string' => 'El campo :attribute debe contener al menos :min caracteres.',
        'array' => 'El campo :attribute no debe contener más de :min elementos.',
    ],
    'not_in' => 'El campo :attribute seleccionado es invalido.',
    'numeric' => 'El campo :attribute debe ser un numero.',
    'present' => 'El campo :attribute debe estar presente.',
    'regex' => 'El formato del campo :attribute es inválido.',
    'required' => 'El campo :attribute es obligatorio',
    'required_if' => 'El campo :attribute es obligatorio cuando el campo :other es :value.',
    'required_unless' => 'El campo :attribute es requerido a menos que :other se encuentre en :values.',
    'required_with' => 'El campo :attribute es obligatorio cuando :values está presente.',
    'required_with_all' => 'El campo :attribute es obligatorio cuando :values está presente.',
    'required_without' => 'El campo :attribute es obligatorio cuando :values no está presente.',
    'required_without_all' => 'El campo :attribute es obligatorio cuando ningún campo :values están presentes.',
    'same' => 'Los campos :attribute y :other deben coincidir.',
    'size' => [
        'numeric' => 'El campo :attribute debe ser :size.',
        'file' => 'El archivo :attribute debe pesar :size kilobytes.',
        'string' => 'El campo :attribute debe contener :size caracteres.',
        'array' => 'El campo :attribute debe contener :size elementos.',
    ],
    'state' => 'El estado no es válido para el país seleccionado.',
    'string' => 'El campo :attribute debe contener solo caracteres.',
    'timezone' => 'El campo :attribute debe contener una zona válida.',
    'unique' => 'El :attribute ya está en uso.',
    'uploaded' => 'El :attribute fallo al subir.',
    'url' => 'El formato de :attribute no corresponde con el de una URL válida.',

    'max_words' => 'El campo :attribute supera el limite de :max palabras.',
    'min_sum' => 'La suma de los campos debe ser mayor o igual a :sum',
    'array_quantity_sum_max' => 'La suma de los campos debe ser menor o igual a :sum',
    'array_quantity_sum_min' => 'La suma de los campos debe ser mayor o igual a :min',

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
        'characterization2.disabilities.*.name' => [
            'required_if' => 'Debe especificar una discapacidad',
        ],
        'characterization2.origins.*.name' => [
            'required_if' => 'Debe especificar un pueblo originario',
        ],
        'characterization2.nationalities.*.name' => [
            'required_if' => 'Debe especificar una nacionalidad',
        ],
    ],


    /*
    |--------------------------------------------------------------------------
    | Custom Validation Attributes
    |--------------------------------------------------------------------------
    |
    | The following language lines are used to swap attribute place-holders
    | with something more reader friendly such as E-Mail Address instead
    | of "email". This simply helps us make messages a little cleaner.
    |
    */

    'attributes' => [
        'name' => 'nombre',
        'g-recaptcha-response' => 'captcha',
        'comunas.region' => 'Región',
        'comunas.comuna' => 'Comuna',
        'instituciones.entidad' => 'Entidad',
        'instituciones.servicio' => 'Servicio',
        'email' => 'correo electrónico',
        'subject' => 'asunto',
        'message' => 'mensaje',
        'dependence' => 'dependencia',
        'code' => 'código',
        'institution.value' => 'establecimiento',
        'admin_name' => 'nombre del administrador',
        'admin_position' => 'cargo del administrador',
        'admin_email' => 'correo electrónico del administrador',
        'admin_phone' => 'teléfono del administrador',
        'admin_password' => 'contraseña del administrador',
        'teacher_password' => 'contraseña del profesor',
        'gender' => 'genero',
        'courses' => 'cursos',
        'course.group' => 'grupo',
        'characterization.nboys' => 'número de niños',
        'characterization.ngirls' => 'número de niñas',
        'characterization2.disabilities.*.id' => 'discapacidad',
        'characterization2.disabilities.*.name' => 'nombre de la discapacidad',
        'characterization2.origins.*.id' => 'pueblo originario',
        'characterization2.origins.*.name' => 'nombre del pueblo originario',
        'characterization2.nationalities.*.id' => 'nacionalidad',
        'characterization2.nationalities.*.name' => 'nombre de la nacionalidad',
        'people' => 'problemas que afectan a personas',
        'people.*.quantity' => 'cantidad de niños y niñas',
        'planet' => 'problemas que afectan al planeta',
        'planet.*.quantity' => 'cantidad de niños y niñas',
        'actions.people' => 'personas => acciones propuestas',
        'actions.planet' => 'planeta => acciones propuestas',
        'people-ods' => 'ámbito personas',
        'people-ods.*.quantity' => 'nº de estudiantes',
        'planet-ods' => 'ámbito planeta',
        'planet-ods.*.quantity' => 'nº de estudiantes',
        'prosperity-ods' => 'ámbito prosperidad',
        'prosperity-ods.*.quantity' => 'nº de estudiantes',
        'actions-ods.people_educative' => 'personas => acciones propuestas para la comunidad educativa',
        'actions-ods.people_authorities' => 'personas => acciones propuestas para las autoridades',
        'actions-ods.planet_educative' => 'planeta => acciones propuestas para la comunidad educativa',
        'actions-ods.planet_authorities' => 'planeta => acciones propuestas para las autoridades',
        'actions-ods.prosperity_educative' => 'prosperidad => acciones propuestas para la comunidad educativa',
        'actions-ods.prosperity_authorities' => 'prosperidad => acciones propuestas para las autoridades',
        'creator' => '¿De quién fue la idea?',
        'involved' => '¿Quienes podrían ayudar?',
        'objetive' => '¿Qué es lo que quiere mejorar?',
        'proposal' => '¿Qué es lo que propone?',
        'results' => '¿Qué efectos positivos se esperan?',
        'thumbnail' => 'Imagen',
        'type_details' => 'Nombre de la Orgranización o Empresa',
        'type' => 'Tipo de Inscripción',
        'terms' => 'Términos y condiciones',
        'year_month' => 'mes a informar',
        'year' => 'año a informar',
        'month' => 'mes a informar'
    ],

];

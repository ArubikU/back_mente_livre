<?php

use App\Core\Router;
use App\Middleware\CorsMiddleware;
use App\Middleware\AuthMiddleware;
use App\Middleware\RoleMiddleware;

/**
 * Configurar rutas de la API
     */
function configureRoutes(Router $router) {
        // Middleware global (CORS siempre se aplica)
    $router->addMiddleware(new CorsMiddleware());

    // Rutas de autenticación (sin auth middleware)
    $router->post('/auth/login', 'App\Controllers\AuthController@login');
        $router->post('/auth/register', 'App\Controllers\AuthController@register');
        $router->post('/auth/check-student', 'App\Controllers\AuthController@checkStudent');
        $router->post('/auth/refresh', 'App\Controllers\AuthController@refresh');
        $router->post('/auth/logout', 'App\Controllers\AuthController@logout');
        $router->post('/auth/forgot-password', 'App\Controllers\AuthController@forgotPassword');
        $router->post('/auth/reset-password', 'App\Controllers\AuthController@resetPassword');
        $router->post('/auth/google', 'App\Controllers\GoogleAuthController@googleLogin');

    // Middlewares reutilizables
    $authMiddleware      = [new AuthMiddleware()];
        $adminMiddleware     = [new AuthMiddleware(), new RoleMiddleware('admin')];
        $staffMiddleware     = [new AuthMiddleware(), new RoleMiddleware('admin', 'therapist')];

    // Rutas de terapeutas (públicas - sin autenticación)
    $router->get('/therapists', 'App\Controllers\TherapistController@index');
        $router->get('/therapists/{id}', 'App\Controllers\TherapistController@show');

    // Rutas de terapeutas (protegidas - solo admin)
    $router->post('/therapists', 'App\Controllers\TherapistController@store', $adminMiddleware);
        $router->put('/therapists/{id}', 'App\Controllers\TherapistController@update', $adminMiddleware);
        $router->delete('/therapists/{id}', 'App\Controllers\TherapistController@destroy', $adminMiddleware);

    // Rutas de citas (appointments) - requieren autenticación
    $router->get('/appointments', 'App\Controllers\AppointmentController@index', $authMiddleware);
        $router->get('/appointments/{id}', 'App\Controllers\AppointmentController@show', $authMiddleware);
        $router->post('/appointments', 'App\Controllers\AppointmentController@store', $authMiddleware);
        $router->put('/appointments/{id}', 'App\Controllers\AppointmentController@update', $authMiddleware);
        $router->delete('/appointments/{id}', 'App\Controllers\AppointmentController@destroy', $authMiddleware);

    // Rutas de usuarios - solo admin
    $router->get('/users', 'App\Controllers\UserController@index', $adminMiddleware);
        $router->get('/users/{id}', 'App\Controllers\UserController@show', $adminMiddleware);
        $router->put('/users/{id}', 'App\Controllers\UserController@update', $adminMiddleware);

    // Rutas de contenido del sitio
    $router->get('/site-content', 'App\Controllers\SiteContentController@show');
        $router->put('/site-content', 'App\Controllers\SiteContentController@update', $adminMiddleware);

    // Rutas de perfiles del equipo (GET público, escritura solo admin)
    $router->get('/team-profiles', 'App\Controllers\TeamProfileController@index');
        $router->get('/team-profiles/{id}', 'App\Controllers\TeamProfileController@show');
        $router->post('/team-profiles', 'App\Controllers\TeamProfileController@store', $adminMiddleware);
        $router->put('/team-profiles/{id}', 'App\Controllers\TeamProfileController@update', $adminMiddleware);
        $router->delete('/team-profiles/{id}', 'App\Controllers\TeamProfileController@destroy', $adminMiddleware);

    // Rutas de códigos promocionales (solo admin)
    $router->get('/promo-codes', 'App\Controllers\PromoCodeController@index', $adminMiddleware);
        $router->get('/promo-codes/{id}', 'App\Controllers\PromoCodeController@show', $adminMiddleware);
        $router->post('/promo-codes', 'App\Controllers\PromoCodeController@store', $adminMiddleware);
        $router->put('/promo-codes/{id}', 'App\Controllers\PromoCodeController@update', $adminMiddleware);
        $router->delete('/promo-codes/{id}', 'App\Controllers\PromoCodeController@destroy', $adminMiddleware);
        $router->post('/promo-codes/validate', 'App\Controllers\PromoCodeController@validate');

    // Rutas de horarios semanales (staff: admin o terapeuta)
    $router->get('/therapists/{therapistId}/schedules', 'App\Controllers\WeeklyScheduleController@index');
        $router->post('/therapists/{therapistId}/schedules', 'App\Controllers\WeeklyScheduleController@store', $staffMiddleware);
        $router->put('/schedules/{id}', 'App\Controllers\WeeklyScheduleController@update', $staffMiddleware);
        $router->delete('/schedules/{id}', 'App\Controllers\WeeklyScheduleController@destroy', $staffMiddleware);

    // Rutas de excepciones de horarios (schedule overrides) (staff)
    $router->get('/therapists/{therapistId}/schedule-overrides', 'App\Controllers\WeeklyScheduleOverrideController@index');
        $router->post('/therapists/{therapistId}/schedule-overrides', 'App\Controllers\WeeklyScheduleOverrideController@store', $staffMiddleware);
        $router->post('/therapists/{therapistId}/schedule-overrides/batch', 'App\Controllers\WeeklyScheduleOverrideController@storeBatch', $staffMiddleware);
        $router->put('/schedule-overrides/{id}', 'App\Controllers\WeeklyScheduleOverrideController@update', $staffMiddleware);
        $router->delete('/schedule-overrides/{id}', 'App\Controllers\WeeklyScheduleOverrideController@destroy', $staffMiddleware);
        $router->delete('/therapists/{therapistId}/schedule-overrides/week', 'App\Controllers\WeeklyScheduleOverrideController@destroyByWeek', $staffMiddleware);

    // Rutas de reglas de dominios de email (solo admin)
    $router->get('/email-domain-rules', 'App\Controllers\EmailDomainRuleController@index', $adminMiddleware);
        $router->get('/email-domain-rules/{id}', 'App\Controllers\EmailDomainRuleController@show', $adminMiddleware);
        $router->post('/email-domain-rules', 'App\Controllers\EmailDomainRuleController@store', $adminMiddleware);
        $router->put('/email-domain-rules/{id}', 'App\Controllers\EmailDomainRuleController@update', $adminMiddleware);
        $router->delete('/email-domain-rules/{id}', 'App\Controllers\EmailDomainRuleController@destroy', $adminMiddleware);

    // Rutas de fotos de terapeutas (staff)
    $router->get('/therapists/{therapistId}/photos', 'App\Controllers\TherapistPhotoController@index');
        $router->get('/therapist-photos/{id}', 'App\Controllers\TherapistPhotoController@show');
        $router->post('/therapists/{therapistId}/photos', 'App\Controllers\TherapistPhotoController@store', $staffMiddleware);
        $router->put('/therapist-photos/{id}', 'App\Controllers\TherapistPhotoController@update', $staffMiddleware);
        $router->delete('/therapist-photos/{id}', 'App\Controllers\TherapistPhotoController@destroy', $staffMiddleware);

    // Rutas de precios de terapeutas (solo admin)
    $router->get('/therapists/{therapistId}/pricing', 'App\Controllers\TherapistPricingController@index');
        $router->put('/therapists/{therapistId}/pricing/batch', 'App\Controllers\TherapistPricingController@updateBatch', $adminMiddleware);
        $router->get('/therapist-pricing/{id}', 'App\Controllers\TherapistPricingController@show');
        $router->post('/therapists/{therapistId}/pricing', 'App\Controllers\TherapistPricingController@store', $adminMiddleware);
        $router->put('/therapist-pricing/{id}', 'App\Controllers\TherapistPricingController@update', $adminMiddleware);
        $router->delete('/therapist-pricing/{id}', 'App\Controllers\TherapistPricingController@destroy', $adminMiddleware);

    // Rutas de roles de usuarios (solo admin)
    $router->get('/users/{userId}/roles', 'App\Controllers\UserRoleController@index', $adminMiddleware);
        $router->post('/users/{userId}/roles', 'App\Controllers\UserRoleController@store', $adminMiddleware);
        $router->delete('/users/{userId}/roles/{roleName}', 'App\Controllers\UserRoleController@destroy', $adminMiddleware);

    // Rutas de subida de archivos (staff)
    $router->post('/upload/therapist-photo', 'App\Controllers\FileUploadController@uploadTherapistPhoto', $staffMiddleware);
        $router->post('/upload/team-photo', 'App\Controllers\FileUploadController@uploadTeamPhoto', $staffMiddleware);

    // Rutas de MercadoPago
    $router->post('/payments/mercadopago', 'App\Controllers\MercadoPagoController@processPayment', $authMiddleware);
        $router->post('/payments/mercadopago/preference', 'App\Controllers\MercadoPagoController@createPreference', $authMiddleware);
        $router->post('/webhooks/mercadopago', 'App\Controllers\MercadoPagoController@webhook');
        $router->get('/payments/mercadopago/public-key', 'App\Controllers\MercadoPagoController@getPublicKey');

    // Rutas de Izipay
    $router->post('/izipay/create-payment', 'App\Controllers\IzipayController@createPayment', $authMiddleware);
        $router->post('/izipay/webhook', 'App\Controllers\IzipayController@webhook');

    // Serve private images from B2 via proxy
    $router->get('/uploads/{path:.+}', 'App\Controllers\ImageController@show');

    // Rutas de Culqi (pasarela embebida)
    $router->post('/payments/culqi', 'App\Controllers\CulqiController@processPayment', $authMiddleware);
        $router->get('/payments/culqi/public-key', 'App\Controllers\CulqiController@getPublicKey');

    // Paquetes de sesiones (solo admin)
    $router->get('/session-packages', 'App\Controllers\SessionPackageController@index', $adminMiddleware);
        $router->post('/session-packages', 'App\Controllers\SessionPackageController@store', $adminMiddleware);
        $router->put('/session-packages/{id}', 'App\Controllers\SessionPackageController@update', $adminMiddleware);
        $router->delete('/session-packages/{id}', 'App\Controllers\SessionPackageController@destroy', $adminMiddleware);

    // Paquetes de pacientes (autenticado)
    $router->get('/patient-packages', 'App\Controllers\PatientPackageController@index', $authMiddleware);
        $router->get('/patient-packages/my-packages', 'App\Controllers\PatientPackageController@myPackages', $authMiddleware);
        $router->post('/patient-packages', 'App\Controllers\PatientPackageController@store', $adminMiddleware);
        $router->put('/patient-packages/{id}', 'App\Controllers\PatientPackageController@update', $adminMiddleware);

    // Documentación API - Swagger UI
    $router->get('/docs', 'App\Controllers\SwaggerController@ui');

    // Generar documentación OpenAPI (automático)
    $router->get('/swagger.json', 'App\Controllers\SwaggerController@get');
        $router->post('/swagger/generate', 'App\Controllers\SwaggerController@generate');
}

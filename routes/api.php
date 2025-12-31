<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\LegalAidClinicController;
use App\Http\Controllers\ProBonoLawyerController;
use App\Http\Controllers\DistrictLitigationOfficerController;
use App\Http\Controllers\ParaLegalVolunteerController;
use App\Http\Controllers\PageHitController;
use App\Http\Controllers\DlsaController;
use App\Http\Controllers\SchemeController;
use App\Http\Controllers\LiteracyClubController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\LocationsController;
use App\Http\Controllers\GrievanceComplaintController;
use App\Http\Controllers\Api\OpenAIController;
use App\Http\Controllers\Api\CatController;
use App\Http\Controllers\Api\ChatController;
use App\Http\Controllers\Api\HcCaseController;
use App\Http\Controllers\Api\OtpController;
use App\Http\Controllers\CourtMapController;
use App\Http\Controllers\ConsumerController;
use App\Http\Controllers\MlaController;
use App\Http\Controllers\DcpuController;
use App\Http\Controllers\OscController;
use App\Http\Controllers\RegistrarController;
use App\Http\Controllers\EstampVendorController;
use App\Http\Controllers\FaqIgrController;
use App\Http\Controllers\EcourtBranchController;
use App\Http\Controllers\NotaryController;
use App\Http\Controllers\StandingCounselController;
use App\Http\Controllers\AdvocateController;
use App\Http\Controllers\LawOfficerController;

// Public routes - OTP endpoints (no authentication required)
// Apply strict rate limiting to prevent abuse
Route::post('/otp/send', [OtpController::class, 'sendOtp'])->middleware('throttle:otp');
Route::post('/otp/verify', [OtpController::class, 'verifyOtp'])->middleware('throttle:otp-verify');
Route::post('/otp/refresh', [OtpController::class, 'refreshToken'])->middleware('throttle:otp');
Route::post('/chat', [ChatController::class, 'chat'])->middleware('throttle:chat');
// Protected routes - All other routes require JWT authentication
// Apply general API rate limiting (60 requests per minute)
Route::middleware(['jwt.auth', 'throttle:api'])->group(function () {
    Route::get('/districts', [LegalAidClinicController::class, 'districts']);
    Route::get('/legal-aid-clinics', [LegalAidClinicController::class, 'index']);

    Route::get('/pro-bono-lawyers', [ProBonoLawyerController::class, 'index']);
    Route::get('/pro-bono-lawyers/districts', [ProBonoLawyerController::class, 'districts']);

    Route::get('/district-litigation-officers', [DistrictLitigationOfficerController::class, 'index']);
    Route::get('/district-litigation-officers/districts', [DistrictLitigationOfficerController::class, 'districts']);

    Route::get('/para-legal-volunteers', [ParaLegalVolunteerController::class, 'index']);
    Route::get('/para-legal-volunteers/districts', [ParaLegalVolunteerController::class, 'districts']);

    Route::get('/dlsa', [DlsaController::class, 'index']);
    Route::get('/dlsa/districts', [DlsaController::class, 'districts']);

    Route::get('/schemes', [SchemeController::class, 'index']);
    Route::get('/schemes/{id}/file', [SchemeController::class, 'file']);

    Route::get('/literacy-clubs', [LiteracyClubController::class, 'index']);
    Route::get('/literacy-clubs/districts', [LiteracyClubController::class, 'districts']);

    Route::post('/contacts', [ContactController::class, 'store']);

    Route::get('/states', [LocationsController::class, 'states']);
    Route::get('/districts-by-state', [LocationsController::class, 'districts']);

    Route::post('/complaints', [GrievanceComplaintController::class, 'store']);
    Route::post('/page-hits', [PageHitController::class, 'store']);

    Route::post('/ai/chat', [OpenAIController::class, 'chat']);


    Route::post('/cat/case-details', [CatController::class, 'caseDetails']);
    Route::post('/cat/daily-orders', [CatController::class, 'dailyOrders']);
    Route::post('/cat/final-orders', [CatController::class, 'finalOrders']);
    Route::get('/cat/cases/search', [CatController::class, 'search'])->middleware('throttle:search');
    Route::get('/hc-cases/search', [HcCaseController::class, 'search'])->middleware('throttle:search');
    Route::get('/courts/coordinates', [CourtMapController::class, 'index']);
    Route::get('/courts/districts', [CourtMapController::class, 'districts']);
    Route::get('/consumers', [ConsumerController::class, 'index']);
    Route::get('/consumers/districts', [ConsumerController::class, 'districts']);
    Route::get('/mlas', [MlaController::class, 'index']);
    Route::get('/dcpu', [DcpuController::class, 'index']);
    Route::get('/dcpu/districts', [DcpuController::class, 'districts']);

    Route::get('/osc', [OscController::class, 'index']);
    Route::get('/osc/districts', [OscController::class, 'districts']);

    Route::get('/registrars', [RegistrarController::class, 'index']);
    Route::get('/registrars/districts', [RegistrarController::class, 'districts']);

    Route::get('/estamp-vendors', [EstampVendorController::class, 'index']);
    Route::get('/estamp-vendors/districts', [EstampVendorController::class, 'districts']);
    Route::get('/faq-igr', [FaqIgrController::class, 'index']);
    Route::get('/ecourt-branches', [EcourtBranchController::class, 'index']);
    Route::get('/notaries', [NotaryController::class, 'index']);
    Route::get('/standing-counsels', [StandingCounselController::class, 'index']);
    Route::get('/standing-counsels/districts', [StandingCounselController::class, 'districts']);
    Route::get('/advocates', [AdvocateController::class, 'index']);
    Route::get('/law-officers', [LawOfficerController::class, 'index']);
});


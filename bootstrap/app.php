<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Auth\AuthenticationException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        channels: __DIR__.'/../routes/channels.php',
        health: '/up',
    )
    ->withSchedule(function ($schedule) {
        // Clean up images older than 90 days daily at 2 AM
        // Only schedule if Supabase is configured
        if (env('SUPABASE_URL') && env('SUPABASE_KEY')) {
            $schedule->command('images:cleanup --days=90')
                ->dailyAt('02:00')
                ->timezone('UTC');
        }
    })
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'role' => \App\Http\Middleware\CheckRole::class,
        ]);
        
        // Configure guest middleware redirect for WEB routes only (not API routes)
        $middleware->redirectGuestsTo(function ($request) {
            // Don't redirect API routes, even if accessed from browser
            if ($request->is('api/*')) {
                return null; // Let exception handler deal with it
            }
            return route('office.login');
        });
        
        // Enable CORS for API routes
        $middleware->api(prepend: [
            \Illuminate\Http\Middleware\HandleCors::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // Handle unauthenticated API requests with JSON response instead of redirect
        // This works even when API is accessed from a web browser
        $exceptions->respond(function ($request, Throwable $e) {
            // Check if it's an API route (even if accessed from browser)
            if ($e instanceof AuthenticationException && $request->is('api/*')) {
                return response()->json([
                    'message' => 'Unauthenticated.',
                    'error' => 'Authentication required'
                ], 401);
            }
        });
        
        // Also handle other exceptions for API routes to return JSON
        $exceptions->shouldRenderJsonWhen(function ($request, Throwable $e) {
            return $request->is('api/*') || $request->expectsJson();
        });
    })->create();

# Project Guidelines - Maintenance API

## Essential Commands
- Run Tests: `php artisan test`
- Run Single Test: `php artisan test --filter=TestName`
- Code Style / Linting: `./vendor/bin/pint`
- Database Reset: `php artisan migrate:fresh --seed`

## Architecture & Coding Rules
- **Controllers:** Keep controllers thin. Delegate business logic to Services or Actions.
- **Form Requests:** Always perform validation using Form Request classes inside `app/Http/Requests/`. Never execute inline `$request->validate()` in controllers.
- **Validation Rules:** Use explicit array syntax for validation rules (e.g., `['required', 'string']`) instead of pipe strings (`'required|string'`).
- **Responses:** Always return standardized API JSON responses using `JsonResponse`.
- **Localization:** Support Arabic attribute translations (`attributes()`) and localized error messages (`messages()`) in Form Requests.

## Strict Restrictions (Forbidden)
- NO inline validation in Controllers.
- NO raw SQL queries without parameter binding.
- NO database operations or Eloquent queries directly inside Controllers or Blade views.
- NO skipping proper Form Request authorization; explicitly return `true` or define Policy rules.
# TODO_FIXPLAN_MANUAL_JSON.md

## Goal
Ensure Manual Compliance Execution endpoints reliably return JSON for AJAX/API calls, avoiding non-JSON HTML responses that break `JSON.parse` in the frontend.

## Done
- Inspected `ManualComplianceExecutionController.php` and routes.
- Updated `findBatchForCurrentUser()` to return a JSON 404 when `$request->expectsJson()` is true.

## Notes / Potential Follow-ups
- The controller currently contains other `auth()->user()` / `auth()->id()` usages which are fine at runtime but may be flagged by static analysis tools.
- There’s an extra blank-line formatting artifact in the controller; can be cleaned up if desired.

## Next (optional hardening)
- Verify frontend callers set `Accept: application/json` (or `X-Requested-With`) so `expectsJson()` returns true.
- Consider using dependency-injected `Request $request` in helper methods to avoid calling global `request()`.


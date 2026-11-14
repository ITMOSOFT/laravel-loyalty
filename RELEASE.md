# Release checklist

1. Run `composer validate --strict`.
2. Run `composer test`.
3. Push the branch and confirm both Laravel 12 and Laravel 13 GitHub Actions jobs pass.
4. Create and push an annotated tag:
   ```bash
   git tag -a v1.0.0 -m "Initial release"
   git push origin v1.0.0
   ```
5. Create a GitHub Release titled `v1.0.0 — Initial Release`.
6. Submit the GitHub repository to Packagist as `itmosoft/laravel-loyalty`.
7. Configure the Packagist GitHub webhook and verify the version.

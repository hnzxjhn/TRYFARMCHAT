# Upgrade Guide

With every upgrade, make sure to re-publish FarmCHAT's assets:

## For v1.2.3 and earlier versions

```
php artisan vendor:publish --tag=FarmCHAT-views --force
php artisan vendor:publish --tag=FarmCHAT-assets --force
```

If needed, you can re-publish the other assets the same way above by just replacing the name of the asset (FarmCHAT-NAME).

## For v1.2.4+ and higher vertions

To re-publish only `views` & `assets`:

```
php artisan FarmCHAT:publish
```

To re-publish all the assets (views, assets, config..):

```
php artisan FarmCHAT:publish --force
```

> This will overwrite all the assets, so all your changes will be overwritten.

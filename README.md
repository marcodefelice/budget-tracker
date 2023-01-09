# budget traker BE module
Opens Source MIT license project. Your Finances in One Place
Set unlimited daily, weekly, monthly, or one-time budgets
See every transaction, synced and categorized automatically.
Get ahead of the curve. Automated upcoming payment notifications.

## About budget traker


## Contributing

Thank you for considering contributing to the Budget tracker The contribution guide can be found in the [Budget tracker documentation](https://www.marcodefelice.it/budget-tracker/be-doc).

## Security Vulnerabilities

If you discover a security vulnerability within Budget tracker, please send an e-mail to [marco.defelice890@gmail.com](mailto:marco.defelice890@gmail.com). All security vulnerabilities will be promptly addressed.

## License

The Budget tracker is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).

# Some develop information
<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400" alt="Laravel Logo"></a></p>

<p align="center">
<a href="https://travis-ci.org/laravel/framework"><img src="https://travis-ci.org/laravel/framework.svg" alt="Build Status"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/dt/laravel/framework" alt="Total Downloads"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/v/laravel/framework" alt="Latest Stable Version"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/l/laravel/framework" alt="License"></a>
</p>

## Stub data structure
.<br>
├── database<br>
│   ├── sql<br>
│   │   ├── account.json<br>
|   |   ├── categories.json<br>
|   |   ├── currency.json<br>
|   |   ├── label.json<br>
|   |   ├── payment_type.json<br>

Every json file must have an array with language key, example [it]<br>

### env configuration mandatory
LANG=it <br>
DROPBOX_ACCESS_TOKEN= <br>
API_KEY = <br>

## command
### php artisan command:importdata
Import a data from CSV with dropbox service or local fs service<br>
with these header:<br>
account,	category,	currency,	amount,	ref_currency_amount,	type,	payment_type,	payment_type_local,	note,	date,	gps_latitude,	gps_longitude,	gps_accuracy_in_meters,	warranty_in_month,	transfer,	payee,	labels,	envelope_id,	custom_category

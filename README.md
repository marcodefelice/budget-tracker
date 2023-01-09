# budget traker FE
Opens Source MIT license project. Your Finances in One Place
Set unlimited daily, weekly, monthly, or one-time budgets
See every transaction, synced and categorized automatically.
Get ahead of the curve. Automated upcoming payment notifications.

## About budget traker

## Contributing

Thank you for considering contributing to the Budget tracker The contribution guide can be found in the [Budget tracker documentation](https://www.marcodefelice.it/budget-tracker/fe-doc).

## Security Vulnerabilities

If you discover a security vulnerability within Budget tracker, please send an e-mail to [marco.defelice890@gmail.com](mailto:marco.defelice890@gmail.com). All security vulnerabilities will be promptly addressed.

## License

The Budget tracker is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).

# Some develop information

## Vue Notus <a href="https://twitter.com/intent/tweet?url=https%3A%2F%2Fdemos.creative-tim.com%2Fvue-notus%2F%23%2F&text=Start%20your%20development%20with%20a%20Free%20Tailwind%20CSS%20and%20VueJS%20UI%20Kit%20and%20Admin.%20Let%20Vue%$
![version](https://img.shields.io/badge/version-1.1.0-blue.svg) ![license](https://img.shields.io/badge/license-MIT-blue.svg) <a href="https://github.com/creativetimofficial/vue-notus/issues?q=is%3Aopen+is%3Aissue" target="_blank">![Git$
![Vue Notus](https://github.com/creativetimofficial/public-assets/blob/master/vue-notus/vue-notus.jpg?raw=true)

### A beautiful UI Kit and Admin for Tailwind CSS and VueJS.

Start your development with a Free Tailwind CSS and VueJS UI Kit and Admin. Let Vue Notus amaze you with its cool features and build tools and get your project to a whole new level.

Vue Notus is Free and Open Source. It features multiple HTML and VueJS elements and it comes with dynamic components for VueJS.

It is based on [Tailwind Starter Kit](https://www.creative-tim.com/learning-lab/tailwind-starter-kit/presentation?ref=vn-github-readme) by Creative Tim, and it is build with both presentation pages, and pages for an admin dashboard.

Speed up your web development with a beautiful product made by <a href="https://creative-tim.com/" target="_blank">Creative Tim </a>.
If you like bright and fresh colors, you will love this Free Tailwind CSS Template! It features a huge number of components that can help you create amazing websites.

### Get Started

- Install NodeJS **LTS** version from <a href="https://nodejs.org/en/?ref=creativetim">NodeJs Official Page</a>
- Download the product on this page
- Unzip the downloaded file to a folder in your computer
- Open Terminal
- Go to your file project (where you’ve unzipped the product)
- (If you are on a linux based terminal) Simply run `npm run install:clean`
- (If not) Run in terminal `npm install`
- (If not) Run in terminal `npm run build:tailwind` (each time you add a new class, a class that does not exist in `src/assets/styles/tailwind.css`, you will need to run this command)
- (If not) Run in terminal `npm run serve`
- Navigate to https://localhost:8080
- Check more about [Tailwind CSS](https://tailwindcss.com/?ref=creativetim)

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
<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400" alt="Laravel Logo"$
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
account,        category,       currency,       amount, ref_currency_amount,    type,   payment_type,   payment_type_local,     note,   date,   gps_latitude,   gps_longitude,  gps_accuracy_in_meters, warranty_in_month,      transfer,   $


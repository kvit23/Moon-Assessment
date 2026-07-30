<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400" alt="Laravel Logo"></a></p>

<p align="center">
<a href="https://github.com/laravel/framework/actions"><img src="https://github.com/laravel/framework/workflows/tests/badge.svg" alt="Build Status"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/dt/laravel/framework" alt="Total Downloads"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/v/laravel/framework" alt="Latest Stable Version"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/l/laravel/framework" alt="License"></a>
</p>

## About the project 
is a simple online store with a few different features
- oredering 
- listing 
- otp auth system with phone number
- mobile sms notificaions 

stack:
- laravel 12 rest API 
- postgres database

installing the project 
- clone from "https://github.com/kvit23/Moon-Assessment.git" 
- copy .env.exampl
- composer install 


end-points (check the API doc file)
------------------
#	Method	Endpoint	Auth
1	POST	/api/v1/auth/register	Public
2	POST	/api/v1/auth/login	Public
3	POST	/api/v1/auth/password/forgot	Public
4	POST	/api/v1/auth/password/verify	Public
5	POST	/api/v1/auth/password/reset	Public
6	GET	/api/v1/auth/products	Public
7	GET	/api/v1/auth/products/{product}	Public
8	DELETE	/api/v1/auth/logout	Token
9	GET	/api/v1/auth/user	Token
10	POST	/api/v1/auth/refresh	Token
11	POST	/api/v1/auth/change-password	Token
12	POST	/api/v1/auth/phone/verify/send	Token
13	POST	/api/v1/auth/phone/verify	Token
14	POST	/api/v1/auth/phone/verify/resend	Token
15	GET	/api/v1/auth/profile	Token
16	PUT	/api/v1/auth/profile	Token
17	GET	/api/v1/auth/orders	Token
18	POST	/api/v1/auth/orders	Token
19	GET	/api/v1/auth/orders/{order}	Token
20	POST	/api/v1/auth/orders/{order}/cancel	Token
21	POST	/api/v1/products/{product}/subscribe	Token
22	DELETE	/api/v1/products/{product}/unsubscribe	Token
23	GET	/api/v1/subscriptions	Token
24	GET	/api/v1/admin/products	Admin
25	POST	/api/v1/admin/products	Admin
26	GET	/api/v1/admin/products/{product}	Admin
27	PUT	/api/v1/admin/products/{product}	Admin
28	DELETE	/api/v1/admin/products/{product}	Admin
29	POST	/api/v1/admin/products/{id}/restore	Admin
30	DELETE	/api/v1/admin/products/{id}/force	Admin
31	GET	/api/v1/admin/orders	Admin
32	GET	/api/v1/admin/orders/{order}	Admin
33	PUT	/api/v1/admin/orders/{order}/status	Admin
34	POST	/api/v1/admin/orders/{order}/cancel	Admin
35	GET	/api/csrf-cookie	-
36	GET	/storage/{path}	-
37	GET	/api/user	Token
38	GET	/	-
39	GET	/api/up	-
40	GET	/api/v1/auth/orders	Token
41	GET	/api/v1/auth/orders/{order}	Token
# Qr-To-Teams
This is a little service to allow a GET request to be transformed into a POST to an MS Teams webhook.

## Getting started
The easiest way to run the app is to use docker compose :
```sh
docker compose up
```
Then you can create an MS teams webhook :
```sh
docker compose exec app php artisan webhook:create
```
That should ask you a couple of questions then print out something like :
```
Webhook created - shortcode is 1WRdqm
```
Now if you open a browser and visit :
```
http://localhost:4444/api/help?text=Help&c=1WRdqm
```
You should get a success message and a webhook should be sent to MS Teams.

## Available CLI commands
```sh
php artisan ...
  webhook:create
  webhook:delete
  webhook:list
  webhook:default
```
You can run those with `--help` to get further information.  If you run `webhook:default` then if the URL doesn't contain the 'c' ("channel") parameter it will fall back to sending to the default webhook.

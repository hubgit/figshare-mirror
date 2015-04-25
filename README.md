# figshare mirror

## Prepare

1. `composer install`
1. `npm install dat -g`
1. `dat init`

## Fetch

1. `php fetch-parallel.php`

## Import

1. `gzcat items.json.gz | dat import --json --primary=article_id`

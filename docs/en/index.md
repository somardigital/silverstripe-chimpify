# Documentation

## Configuration

Add your MailChimp API key to the SilverStripe config.

__mysite/_config/config.yml__

```yml
ChimpifyCampaign:
  api_key: 'YOUR_MAILCHIMP_API_KEY'
```

Extend `Blog` with `ChimpifyContentSourceExtension`. This provides Blog with the `$has_one` relation
required by `ChimpifyCampaign`.

__mysite/_config/config.yml__

```yml
Blog:
  extensions:
    - ChimpifyContentSourceExtension
```

Add an [editable content area](http://kb.mailchimp.com/templates/code/create-editable-content-areas-with-mailchimps-template-language)
named `chimpify` to your MailChimp Template. This is where your Blog content will be populated.

```html
<div mc:edit="chimpify">
    This will be replaced with Blog content.
</div>
```

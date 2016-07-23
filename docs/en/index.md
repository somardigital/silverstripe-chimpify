# Documentation

## Installation

`composer require somardesignstudios/silverstripe-chimpify`

## Configuration

### SilverStripe

Add your MailChimp API key and the required `$belongs_many_many` relation to
`mysite/_config/config.yml`

```yml
ChimpifyCampaign:
  api_key: 'YOUR_MAILCHIMP_API_KEY'

Blog:
  extensions:
    - ChimpifyContentSourceExtension
```

### MailChimp

Add an [editable content area](http://kb.mailchimp.com/templates/code/create-editable-content-areas-with-mailchimps-template-language)
named `chimpify` to your MailChimp Template. This is where your Blog content will be populated.

```html
<div mc:edit="chimpify">
    This will be replaced with Blog content.
</div>
```

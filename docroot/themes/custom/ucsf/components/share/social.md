# Example of usage:

## social.twig

```twig
{% include '@evolve/share/social.twig' with {
  social_url: url('<current>'),
  summary: _insight.body.value,
  title: _insight.title,
  {# if mail icon required add: #}
  mail: 1
} %}
```

## social-info.twig

social-info creates links to the social information of a especific user.
data like twitter, linkedin and email should be provided.
all links will open in new tabs.
this component is used in the node--person-full.html.twig for example.

Notes: use absolute strings like: https://twitter.com/SOME_NAME

```twig
{% set linkedin = _person.field_linkedin.url %}
{% set twitter = _person.field_twitter.url %}
{% set mail = _person.field_email %}
{% include '@evolve/share/social--info.twig' %}
```

Or

```twig
{% include '@evolve/share/social--info.twig' with {
    linkedin: "https://linkedin.com/in/someuser",
    twitter: "https://twitter.com/someuser }}",
    mail: "someuser@example.com
} %}
```

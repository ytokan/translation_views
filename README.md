Translation Views
=============

INTRODUCTION
------------
Provides fields and filter for views to display information about the current row, the **source** translation, in another **target** language. You can also add translation operation links to decide if you want to add/edit the content in the target language.

Configurations for a demo view is imported on installation, named `Content translation jobs` which can be found at path `/translate/content`. If you want to get view for other translatable entities, you should build it yourself using our fields/filters.

**Notice**: This module provides view's fields/filters only for translatable entity types, this means that until you have enabled content translation support for an entity you'll not get provided any extra fields/filters. The content translations can be enabled at path `admin/config/regional/content-language`

FEATURES
------------

**Target translation**

- Target language: `field`,`filter`. Should in most cases be an exposed filter.
  Also, there is an extra filter option: *Remove rows where source language is equal to target language*

These fields/filters will display information about the current content in the selected target language:
- Translation outdated: `field`,`filter`
- Translation status: `field`,`filter`
- Target language equals default language: `field`,`filter`: Checks if the target language is the same as the original language of the node.
- Translation changed time: `field`
- Translation source equals row language: `field`,`filter`: Checks if the source translation of the target language is the same as the language of the row
- Translation operations: `field`
- Translation moderation state: `field`

**Source translation**
- Translation counter: `field`,`filter`: count translations of oirignal language. You can also configure the field to include original language in the count.

REQUIREMENTS
------------
- Drupal 8
- PHP 7+

INSTALLATION
------------
Install module as usually.

CONFIGURATION
-------------
No configuration is needed.

### Demo view: Content translation jobs:
Upon installation, a new view is added named `Content translation jobs` that demonstrate how the fields/filters can be used to create lists of translation jobs. Available at path `/translate/content`.

If this didn't happened on installation then go to the`admin/config/development/configuration/single/import` and manually import config from the file located in the module folder at: `config/optional/views.view.content_translations.yml`.

**Fields**
The view display:
- Title for each node in the view
- The node language
- The target language
- The translation status of target language.
- The time the content was last changed in target language
- Translation operations to add/edit translation in target language

**Filters**
Exposed filters:
- Source language (the language of each node in the view)
- Target language
- Outdated status in target language
- Translation status in target language

Hidden filters
- Source content outdated must be false: Make sure you do not translation from outdated nodes.
- Target language equals default language must be false: As there is no use to check status of original language as a target language.
- The target language must be untranslated or outdated: Make sure translated and updated content disappear from the list.
- The target language must be untranslated or the same as the source language of translation: This makes sure that once there is a translation in target language it will only be displayed if the source language is the correct source language. If target language is not translated you can use different language as source translation.

MODULE INTEGRATION
-------------
You can limit the target language filter list to the users registered translation skill if you're using [Local Translation](https://www.drupal.org/project/local_translation)

MAINTAINERS
-----------
Developed by [vlad.dancer](https://drupal.org/u/vladdancer)  
Designed by [matsbla](https://drupal.org/u/matsbla)

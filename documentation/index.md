# Class description

## PostSynchronization\Initializer

It initializes the whole logic based on the hooks.

## PostSynchronization\CustomBox

It has two responsibilities:

1. Displaying the config box on the post edit where a user may define which site a page should be synchronized to
2. Updates those settings when a page is saved

**TODO**
1. Change checkbox into radio in order to allow only single-site selection
2. Move hooks definition inside this class from Initializer

## OnPostSave

It performs post synchronization

## Redirections

1. Replaces links on the site to target a proper site instead of local site
2. When a redirected page is visited by usee via a local site, a user is redirected to the correct site.


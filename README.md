# Important Notice

This is a fork of the original SVN-Repository residing at the following locations:
- https://svn.liip.ch/repos/public/fluxcms/trunk/
- https://svn.liip.ch/repos/public/fluxcms_demo/trunk/

It has been extended and might not be fully backwards compatible.

See https://fosswiki.liip.ch/display/FLX/Home for further information about Flux CMS


# Installation

First clone the git repository, cd into the repository, then do the following:

    git submodule init
    git submodule update

    chmod a+rw .
    chmod -R a+rw tmp/

Then launch the installer located at /install.

# Braking Changes in DBForms2

The dbforms2 part of this fork has been notably extended and introduces changes
which are not fully backwards compatible to existing forms.

## List of New Features
- Fields can be organized in sections, sections are displayed as tabs
- n2m relations can be sorted using drag and drop
- New entries to n2m relations can be added on the fly
- Multiple values can be selected in select fields
- Use jQuery UI widgets and interactions (calendar, drag and drop)
- Replaced YUI with jQuery

# List of Other New Features
- **dynimage**: new filter _megacrop_ featuring auto crop, auto center, auto rotation and more s. [the source](blob/master/inc/bx/dynimage/filters/gd/megacrop.php) for more information.

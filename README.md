# General Notice

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

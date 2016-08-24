Explanation of CSS Structure:


Fusion Admin was built using SASS styling. Therefor nearly all of the themes styles have been seperated
by category, placement, or purpose so that the stucture is modular and easy to follow.

However if you are not using SASS you will have to rely heavily on the comments left in all of the
stylesheet pages and folders. You will find that all of the sites stylesheets have been combined into 4 
large files. It is important that you go through and remove various plugins and styles you will not be
using. Make sure to create backups and to open any Readme.txt you might encounter.


SASS Structure:


theme.scss <----- Theme Folder <-----  All Files + Components Folder


utility.scss <----- Utility Folder <------ All Files


vendor.scss <----- Vendor Folder <-----  editors.scss + fonts.scss + plugins.scss
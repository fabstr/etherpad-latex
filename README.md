# etherpad-latex
A set of php and javascript files to combine etherpad-lite with LaTeX.

## Setup
Install [etherpad-lite](http://etherpad.org/) and get the api key from 
APIKEY.txt. Install a TeX distribution on the server (e.g 
[TexLive](https://www.tug.org/texlive/)).

### Server-side configuration
Two files should be edited in the backend folder:
- `backend/app/config/database.php`
- `backend/.env.php`
Read the comments and enter sensible values.

#### Improving security
To only allow the TeX distribution to see files in `$TEXMFOUTPUT` (or a 
subdirectory), change your `texmf.cnf` and set this row:
```
openin_any = p
```

`texmf.cnf` can be found by running 
```
$ kpsewhich texmf.cnf
```

If this change is not made, malicious users might have read access to your 
server.


### Client-side configuration
`ETHERPADHOST` and `ETHERPADSETTINGS` in `js/settings.js` should be set:

- `ETHERPADLITEHOST` is the web address where the client can reach the 
etherpad-lite installation
- `ETHERPADSETTINGS` is a json object that sets the embed parameters for 
etherpad-lite (see [the etherpad-lite 
wiki](https://github.com/ether/etherpad-lite/wiki/Embed-Parameters))
- `HOSTURL` is the url to where etherpad-latex is installed. (ie 
https://example.com/etherpadlatex").

## How does it work?
`edit.php` consists of two iframe's: etherpad-lite to edit and ViewerJS to view
the pdf. When the user presses compile, the php script `etherpad_latex.php` 
downloads the contents of the etherpad document and saves it to a `.tex` file 
in a directory unique to the document. `latemk` is used to compile and the 
ViewerJS iframe is updated to show the updated pdf (which is served by 
`pdf.php`).

## License 
etherpad-latex is available under GPLv3.

### Libraries used
- jQuery and jQuery UI are relased under MIT.
- PDF.js is released under Apache

# W3C Public Mailing Lists Reader

This site is a snapshot of a few public mailing lists hosted by the W3C.
On modern browsers it also provides an enhanced viewing mode.

You can enter the lists on the local server at [/Archives/Public/](/Archives/Public/).

The canonical lists are at [https://lists.w3.org/Archives/Public/](https://lists.w3.org/Archives/Public/) 

## Browser support

Older and noscript browsers get a basic view (a bit like Reader Mode). 
Contemporary browsers have an enhanced view - 
it seems ok when I've tested on real machines in Firefox, Chrome, Edge and Safari,
but I have noticed a few failures in Safari and Edge when testing in BrowserStack.

You can always append `?no_boot` to the URL of any page to see the basic view for that page. 
It would be better if the UI offered the user the chance to revert to basic view.


## Design

(If you look at the code you will think it is a big-ball-of-mud, 
but there is an element of design underneath it all.)

### Front end

The front-end is fairly simple. 

- When the landing-page loads, a trivial inline script checks 
   if this is a contemporary browser.
- If it is then it kicks off the enhancement process.
- The enhanced view is three panes where each is populated with content coming from 
    1. the list of mailing-lists
    2. the index of a particular mailing-list
    3. the message the user has selected to view
- If the landing-page is #1 then the other two aren't initially loaded.
    Likewise, if it is #2 then pane #3 is initially empty.
- After this the front-end just has to intercept navigation requests triggered 
    when the user activates a hyperlink. 
    Instead of reloading the page the response is processed and 
    inserted in the appropriate pane. (Like frameset only evolved).


### Back-end

Because the enhanced view is composed in the browser 
the back-end is just a bit more than a mirror.
The "trickiest" part is that the separate mailing lists
the origin site - [https://lists.w3.org](https://lists.w3.org) -
split messages up into periods but don't directly link between periods. 


#### Installation

At installation it does a bit of intelligent spidering 
to build databases of periods and pages and 
to find the crosslinks between periods and also likely thread-starter messages.
This spidering also starts to build up a local cache so some requests 
won't need to be forwarded to the origin server. 

- The list of lists page (/Archives/Public/) is reduced to 
    a smaller list of interesting (and short) mailing lists
- For each mailing list
    + The main index (/Archives/Public/<list>/) is read to identify calendar periods 
      that actually have content
    + The thread sorted index for each period 
      (/Archives/Public/<list>/<period>/thread.html) 
      is read to identify contempory roots of threads
    + Contemporary root messages that also have a subject starting with "RE:" 
      are loaded to check whether they reply to a message in the same list
      but from an earlier period. This requires a 
      [Message ID Resolver](https://www.w3.org/mid/).
    - If it does then the messages are added to each-others records in the database.


#### File serving

(Like I said - almost a mirror-site).

- All pages served by the site are merely transformed versions of those on the origin.
- The installation spidering enters almost all the index pages into the local cache.
    Most message pages are fetched the first time a user requests them.
- The list of lists page is generated from the database with php rendering.
- For each mailing list
    + The main index is generated from the database as a table of months against years
      (Again php-rendering)
    + The date sorted indexes are fetched as requested and transformed with XSLT.
    + The new threads indexes are generated from the data base with php rendering.
    + Message pages are fetched as requested and scraped and crosslinked 
      (if appropriate) and the resultant content and metadata, etc 
      is rendered with php.

- When any page is rendered the result is stored on the filesystem
   so future requests are just serving static files.


### Tools and Libraries

- PHP
- Slim Framework
- Guzzle HTTP
- Monolog
- HyperFrameset


## TODO

(So many things)

- Some messages on the origin have character-sets other than UTF-8 but we always 
    pretend it is UTF-8. So much for being a smart mirror.
- If the landing-page is a message then the appropriate list is loaded into the 
   thread-pane but the focus in the thread-pane should then move to the line that
   matches the message.
- Some messages have really long lines and are not wrapped in the message-pane.
- The CSS for individual pages and the enhanced view were implemented completely ad hoc.
- The user may want to hide/show the left and top panes - 
    add some toggle icons to the banner bar.
- On small screens the left and top panes should be hidden by default 
    and shown as overlays if the user enables them
- Keep author and date in columns aligned to the right
- In the threads pane a message may have an expander arrow (indicating it has replies)
    but toggling it reveals (eventually) there are no replies. 
    (The spidering is not intelligent enough)
- When all three panes have content and you click in the left pane to load a different 
    list then the message pane stays with the old message.
- When navigating using the back and forwards button 
    the threads pane can get out of sync.


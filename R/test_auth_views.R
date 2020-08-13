library(httr)
library(readr)
site <- 'http://deq1.bse.vt.edu/d.alpha';
#Cross-site Request Forgery protection (Token needed for POST and PUT)
csrf <- GET(
  url=paste(site, '/restws/session/token/',sep=''),
  authenticate(rest_uname, rest_pw)
);
token <- content(csrf)
hydroid=147;
# without authentication
sp <- GET(
  paste(site,"export-dhts-fid-varkey",hydroid,sep="/"), 
  encode = "xml"
);

#print(paste("Property Query:",sp,""));
noauth <- content(sp);
noauth


# WITH authentication
# but actually, due to cookies/IP stuff drupal doesn't require the 
# CSRF token because the above one works as well.
sp <- GET(
  paste(site,"export-dhts-fid-varkey",hydroid,sep="/"),
  add_headers(HTTP_X_CSRF_TOKEN = token),
  encode = "xml"
);

auth <- content(sp);
auth

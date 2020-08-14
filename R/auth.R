# Use REST
library(httr)
library(readr)

rest_token <- function (site) {
  rest_uname <- readline(prompt="Enter user name: ")
  rest_pw <- readline(prompt="Enter Password: ")
  
  #Cross-site Request Forgery protection (Token needed for POST and PUT)
  csrf <- GET(
    url=paste(site, '/restws/session/token/',sep=''),
    authenticate(rest_uname, rest_pw)
  );
  token <- content(csrf)
  return(token)
}

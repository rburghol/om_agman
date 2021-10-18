library("hydrotools")
library("httr")

basepath = "/var/www/R"
source(paste(basepath,"config.R", sep="/"))

ds <- RomDataSource$new("http://deq1.bse.vt.edu/d.dh", rest_w_uname)
ds$get_token(rest_w_pass)

# REST and dh_timeseries_weather
# Not yet working?
dhw <- hydrotools::fn_get_rest(
  'dh_timeseries_weather', 
  'tid', 
  list(featureid = 378700, tsendtime = 1628568000, entity_type = 'dh_feature'), 
  site = site, 
  token
)


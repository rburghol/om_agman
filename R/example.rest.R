site = "http://www.grapeipm.org/d.live"
pg = "dh_timeseries/120454332/trends/147/agman_fruit_chem/export/agman_fruit_chem/1546318800/1577768400"

url = paste(site,pg,sep="/")

dat <- read.delim(url, header = TRUE)

dat$jday <- as.integer(format(as.Date(dat$date), "%j"))
loessMod25 <- loess(total_sugar_mgb ~ jday, data=dat, span=0.25) # 10% smoothing span
smoothed25 <- predict(loessMod25) 

plot(total_sugar_mgb ~ jday, data=dat)
lines(smoothed25, x=dat$jday, col="green")

-- find mis-varid'ed insect events
select varid, count(*) from dh_timeseries where varid = 234 and tscode in ('cutworms' ,  'bmsb' , 'gbm', 'glh','swd', 'gfb', 'rblr', 'yj', 'rose_chafer', 'gcurculio','glooper', 'jbeetle', 'ermite', 'tgallmaker', 'grb', 'gcg', 'mb')  group by varid;

--#fix them 
update dh_timeseries set varid = 91 where varid = 234 and tscode in ('cutworms' ,  'bmsb' , 'gbm', 'glh','swd', 'gfb', 'rblr', 'yj', 'rose_chafer', 'gcurculio','glooper', 'jbeetle', 'ermite', 'tgallmaker', 'grb', 'gcg', 'mb');
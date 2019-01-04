Sub Graph_model()
On Error Resume Next         'this will skip any errors !!!!
'
' Graph_model Macro
' Macro recorded 12/14/2010 by jferguson
'
' Keyboard Shortcut: Ctrl+g

' delete any existing charts
 Worksheets("model_output").ChartObjects.Delete
 
'
    Range("E:E,G:G,H:H,I:I,J:J,K:K").Select
    Range("I1").Activate
    Charts.Add
    ActiveChart.ChartType = xlXYScatterSmooth
    ActiveChart.SetSourceData Source:=Sheets("model_output").Range( _
        "E:E,G:G,H:H,I:I,J:J,K:K"), PlotBy:=xlColumns
    ActiveChart.Location Where:=xlLocationAsObject, Name:="model_output"
    
    ' form the title
    titlea$ = Sheets("model_output").Cells(2, 1)
    titleb$ = Sheets("model_output").Cells(2, 2)
    titlec$ = Sheets("model_output").Cells(2, 3)
    titled$ = Sheets("model_output").Cells(2, 12)
    title1$ = titlea$ + ",  " + titleb$ + "  " + titlec$ + ", Budbreak predicted on " + titled$
    
    With ActiveChart
        .HasTitle = True
        .ChartTitle.Characters.Text = title1$
        .ChartTitle.Font.Size = 12
        .HasAxis(xlCategory, xlPrimary) = True
        .HasAxis(xlValue, xlPrimary) = True
    End With
    
    
    
    With ActiveChart.Axes(xlCategory)
        .MinimumScale = 250
        .MaximumScale = 500
        
    
        '.Axes(xlCategory, xlPrimary).HasTitle = True
        '.Axes(xlCategory, xlPrimary).AxisTitle.Characters.Text = "day"
        '.Axes(xlValue, xlPrimary).HasTitle = True
        '.Axes(xlValue, xlPrimary).AxisTitle.Characters.Text = "temp"
    End With
    
    With ActiveChart.Axes(ylCategory)
        .MinimumScale = -30
        .MaximumScale = 40
        
    End With
    
    ' Max air temp marker and line
    With ActiveChart.SeriesCollection(1)
        .Format.Line.Visible = msoFalse
        .Format.Line.Visible = msoTrue
        .Format.Line.ForeColor.RGB = RGB(0, 0, 0)
        .Format.Line.Weight = 1
        .MarkerBackgroundColorIndex = 2
        .MarkerForegroundColorIndex = 1
    End With
    
    
    'min air temp marker and line
    With ActiveChart.SeriesCollection(2)
        .Format.Line.Visible = msoFalse
        .Format.Line.Visible = msoTrue
        .Format.Line.ForeColor.RGB = RGB(0, 0, 0)
        .Format.Line.Weight = 1
        .MarkerBackgroundColorIndex = 2
        .MarkerForegroundColorIndex = 1
        .MarkerSize = 4
    End With
    
    'Predicted LT50 line and marker
    With ActiveChart.SeriesCollection(3)
        .MarkerStyle = none
        .Format.Line.Visible = msoFalse
        .Format.Line.Visible = msoTrue
        .Format.Line.ForeColor.RGB = RGB(0, 0, 0)
        .Format.Line.Weight = 2
        
    End With

    
    
    
    'LT50 Observation marker
    With ActiveChart.SeriesCollection(4)
        .Format.Line.Visible = msoFalse
        .MarkerBackgroundColorIndex = 2
        .MarkerForegroundColorIndex = 1
        .MarkerSize = 4
    End With
    
    
    ' Budbreak Marker
    With ActiveChart.SeriesCollection(5)
        .Format.Line.Visible = msoFalse
        .MarkerBackgroundColorIndex = 10
        .MarkerForegroundColorIndex = 10
        .MarkerSize = 5
    End With

    
    Sheets("model_output").Activate
    Range("d6").Select
    
End Sub


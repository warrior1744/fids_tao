$(document).ready(function(){

    var FREQ = 16000;  //default is 16000
    var FREQ2 = 8000;

    function startAJAXcalls(){
      setTimeout(function(){
          getData_FIDS_tao_txt('Page 1');
          startAJAXcalls();
      }, FREQ);

      setTimeout(function(){
        getData_FIDS_tao_txt('Page 2');
      }, FREQ2);
    }

    getData_FIDS_tao_txt('Page 1');
    startAJAXcalls();
     
  function getData_FIDS_tao_txt(page)
  {
    $(function(){
      var myAjax= 
      {
        init: function(){
                        $.ajax({
                            method:'POST',
                            cache:false,
                            mimeType:"text/html ;charset=UTF-8" ,  
                            url: 'processText.php',
                            dataType: 'json',
                            async: true
                        }).done(function(json){
                          $('#fidsTable').empty();
                          let info = '<colgroup><col style="width: 8%;">'+
                                               '<col style="width: 30%;">'+
                                               '<col style="width: 14%;">'+
                                               '<col style="width: 13%;">'+
                                               '<col style="width: 10%;">'+
                                               '<col style="width: 15%;">'+
                                               '<col style="width: 10%;"></colgroup>'+
                                     '<tbody><tr><th id="th_time">時間 <br> Time</th>'+
                                                '<th id="th_destination">目的地 <br> Destination</th>'+
                                                '<th id="th_airlines">航空公司 <br> Airlines</th>'+
                                                '<th id="th_flightno">班次 <br> Flight No.</th>'+
                                                '<th id="th_gate">登機門 <br> Gate</th>'+
                                                '<th id="th_remarks">備註 <br> Remarks</th>'+
                                                '<th id="th_page">Page 1</th></tr>';
                          let jsonObj = (page =='Page 1')? json.page1: json.page2; let chg = 0;
                          $.each(jsonObj, function(){
                            let newRemarkClass='';
                            if(this['flight_status'] == '出發Departed'){this['flight_status'] = '出發 <br> Departed'; newRemarkClass = 'remark-green';
                            }else if(this['flight_status'] == '客機載貨Cargo Only'){this['flight_status'] = '客機載貨 <br> Cargo Only'; newRemarkClass = 'remark-blue';
                            }else if(this['flight_status'] == '準時On Time'){this['flight_status'] = '準時 <br> On Time'; newRemarkClass = 'remark-blue';
                            }else if(this['flight_status'] == '取消Cancelled'){this['flight_status'] = '取消 <br> Cancelled'; newRemarkClass ='remark-red';
                            }else if(this['flight_status'] == '時間更改SCHEDULE CHANGE'){this['flight_status'] = '改時 <br> Time Chg'; newRemarkClass ='remark-red';
                            }else if(this['flight_status'] == '出發DEPARTED'){this['flight_status'] = '出發 <br> Departed'; newRemarkClass = 'remark-green';
                            }else if(this['flight_status'] == '準時ON TIME'){this['flight_status'] = '準時 <br> On Time'; newRemarkClass = 'remark-blue';
                            }else if(this['flight_status'] == '取消CANCELLED'){this['flight_status'] = '取消 <br> Cancelled'; newRemarkClass ='remark-red';
                            }else{newRemarkClass ='remark-purple';}    
                            info+= '<tr class="fidsRow">'+
                            '<td class="fidsTime_td" >'+this['official_time']+'</td>'+
                            '<td class="fidsDestination_td" id="change'+chg+'">'+this['destination_cht']+' '+this['destination_eng']+'</td>'+
                            '<td class="fidsAirlines_td">'+this['airline_cht']+'</td>'+
                            '<td class="fidsFlightNo_td">'+this['IATA']+' '+this['flight.No']+'</td>'+
                            '<td class="fidsGate_td"><span class="fidsGate_span badge badge-light">'+this['gate']+'</span></td>'+
                            '<td class="fidsRemarks_td '+ newRemarkClass +'">'+this['flight_status']+'</td>';
                            
                            if(this['official_time'] != this['actual_time']){
                              info+= '<td class="fidsOther_td">'+ this['actual_time']+'</td></tr>';
                            }else{
                              info+= '<td class="fidsOther_td"></td></tr>';
                            }
                            if (!(this['othersites_cht'] ==="") || !(this['othersites_eng']=== ""))
                            {
                              $othersites = this['othersites_cht']+' '+this['othersites_eng'];
                              let fidsDestination_change = '#change'+chg;
                              setTimeout(function(){
                              $(fidsDestination_change).text($othersites);//only one line, not all
                              }, 4000);
                            }
                            chg++;
                          });//end each
                          
                          info+= '</tbody>';
                          $('#fidsTable').append(info);
                          $('#th_page').text(page);//finally we need to change some text after the info is loaded
                         
                        }).fail(function(jqXHR, textStatus, errorThrown){
                            console.log('jqXHR: '+jqXHR+', textStatus: '+textStatus+', errorThrown: '+errorThrown);
                          });
                    }//end init:
                  };//end variable myAjax
                  myAjax.init();
      });
  }//end getData()
    // showFrequency();
    // function showFrequency(){
    //    console.log("Page refreshes every "+ FREQ/1000 +" second(s)." )
    // }
});//end $(document).ready(function(){


function createRequest() {
    try {
      request = new XMLHttpRequest();
    } catch (tryMS) {
      try {
        request = new ActiveXObject("Msxml2.XMLHTTP");
      } catch (otherMS) {
        try {
          request = new ActiveXObject("Microsoft.XMLHTTP");
        } catch (failed) {
          request = null;
        }
      }
    }
    return request;
  }


  function displayDetails(){
    if (request.readyState == 4) {
        if (request.status == 200) {
            let response = request.responseText;
            console.log(response);
        }
    }
}


function onlySpaces(str) {
  return str.trim().length === 0;
}
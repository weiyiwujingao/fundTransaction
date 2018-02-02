<!--roll-info End-->
<!--main-info Start-->
<style>
    .buxian {
        background: none !important;
    }
</style>
<div class="main-info">
    <div class="miTitle">
        基金筛选
    </div>
    <div class="miBottom">
        <div class="miBCon jjsx">
            <div class="choose">
                <div class="choosed">
                    　已选条件：

                </div>
                <div class="jjType condition">
                    　基金类型：
                    <?php $fund_types = array(
                        'kfsqb'   => '不限',
                        'kfsgpx'  => '股票型',
                        'kfszqx'  => '债券型',
                        'kfshhx'  => '混合型',
                        'kfsbbx'  => '保本型',
                        'kfszsx'  => '指数型',
                        'kfsqdii' => 'QDII',
                        'kfsetf'  => 'ETF联接',
                        'kfslof'  => 'LOF',

                    ) ?>
                    <?php foreach ($fund_types as $k => $fund_type): ?>
                        <a href="javascript:;"
                           val="<?php echo $k; ?>" <?php if ($type == $k): ?> class="<?php if($fund_type != '不限') echo "active" ?>" <?php endif; ?>><?php
                            echo $fund_type ?></a>
                    <?php endforeach; ?>
                </div>
                <div class="aYear condition">
                    近一年业绩：
                    <!--                    <a href="javascript:;" class="marginLeft0 unlimited ">不限</a>-->
                    <?php $fund_performance = array(
                        ''      => '不限',
                        '0-10'  => '0~10%',
                        '11-20' => '11%~20%',
                        '21-50' => '21%~50%',
                        '50'    => '50%以上',
                        'fsy'     => '负收益',
                    ) ?>
                    <?php
                    foreach ($fund_performance as $k => $fund_per):

                        ?>
                        <a href="javascript:;" val="<?php echo $k ?>"
                           <?php if ($per == $k): ?>class="<?php if($fund_per != '不限') echo "active" ?>" <?php endif;
                        ?>><?php echo $fund_per ?> </a>
                    <?php endforeach; ?>

                </div>
                <div class="jjCompany">
                    　基金公司：
                    <?php
                    array_unshift($company, array(
                        'InvestAdvisorCode' => '',
                        'InvestAdvisorName' => '不限'
                    ));
                    $first_3_companies = array_splice($company, 0, 4);
                    ?>
                    <?php foreach ($first_3_companies as $single_company): ?>
                        <a href="javascript:;" val="<?php
                        echo $single_company['InvestAdvisorCode']; ?>"
                           <?php if ($com == $single_company['InvestAdvisorCode']): ?>class="companyName <?php if($single_company['InvestAdvisorName'] != '不限') echo "active" ?>"<?php endif; ?>><?php echo $single_company['InvestAdvisorName']; ?></a>
                    <?php endforeach; ?>
                    <em class="showMore">更多</em>
                    <div class="jjCompany hideJJ">

                        <p>
                            <?php
                            foreach ($company as $v):
                                ?>
                                <a href="javascript:" id="last" val="<?php echo $v['InvestAdvisorCode']; ?>"
                                   <?php if ($com == $v['InvestAdvisorCode']): ?>class="marginLeft0 companyName active" <?php endif;
                                ?>><?php echo $v['InvestAdvisorName'] ?></a>
                            <?php endforeach; ?>
                        </p>
                    </div>
                </div>
                <div class="jjValue condition">
                    　基金净值：
                    <!--                    <a href="javascript:;" class="marginLeft0 unlimited ">不限</a>-->
                    <?php $fund_net = array(
                        ''        => '不限',
                        '0-0.5'   => '0~0.5',
                        '0.6-1.0' => '0.6~1.0',
                        '1.1-1.5' => '1.1~1.5',
                        '1.6-2.0' => '1.6~2.0',
                        '2.0'     => '2.0以上',
                    );
                    ?>
                    <?php foreach ($fund_net as $k => $fund_ne):
                        ?>
                        <a href="javascript:;"
                           val="<?php echo $k; ?>" <?php if ($net == $k): ?> class="<?php if($fund_ne != '不限') echo "active" ?>"
                        <?php endif;
                           ?>><?php echo $fund_ne ?></a>

                    <?php endforeach; ?>
                </div>
                <a href="javascript:;" class="startBtn">开始筛选</a>
            </div>
            <div class="tableJJSXBox">
                <table cellspacing="0" cellpadding="0" border="0" class="tableJJSXa" width="100%">
                    <tbody>
                    <tr class="tr1">
                        <td width="52" height="54" class="bg-gray">关注</td>
                        <td width="48" class="bg-gray">比较</td>
                        <td width="72" class="bg-gray">基金代码</td>
                        <td width="137" class="bg-gray">基金名称</td>
                        <td width="60" class="bg-gray">类型</td>
                        <td width="74" class="bg-gray">单位净值</td>
                        <td width="71" class="bg-gray">累计净值</td>
                        <td width="96" class="bg-gray">日期</td>
                        <td width="63" class="bg-gray font-blue <?php if (strpos($order['type'], 'march') !== false) {
                            echo $order['sort'] == 'desc' ? 'down' : 'up';
                        } ?>">
                            <a href="<?php echo $order['links']['march'] ?>">近3月</a>
                        </td>
                        <td width="57" class="bg-gray font-blue <?php if (strpos($order['type'], 'june') !== false) {
                            echo $order['sort'] == 'desc' ? 'down' : 'up';
                        } ?>">
                            <a href="<?php echo $order['links']['june'] ?>">近6月</a>
                        </td>
                        <td width="62" class="bg-gray font-blue <?php if (strpos($order['type'], 'year') !== false) {
                            echo $order['sort'] == 'desc' ? 'down' : 'up';
                        } ?>">
                            <a href="<?php echo $order['links']['year'] ?>">近1年</a></td>
                        <td width="46" class="bg-gray">费率</td>
                        <td width="53" class="bg-gray">操作</td>
                    </tr>
                    <?php foreach ($list as $k => $v): ?>
                        <tr>
                            <td height="39" class="follow"><span onclick="Add1(this)"></span></td>
                            <td><input type="checkbox" class="Chk" id="jj1a<?php echo $offset+$k+1 ?>"/></td>
                            <td class="jjName"><a target="_blank"
                                                  href="/fund/<?php echo $v['SecurityCode'] ?>"><?php echo $v['SecurityCode'] ?></a>
                            </td>
                            <!--                        --><?php //if($v['SecurityCode'] ) :?>
                            <td class="jjName"><a title="<?php echo $v['ChiNameAbbr'] ?>" target="_blank"
                                                  href="/fund/<?php echo $v['SecurityCode'] ?>"><?php echo $v['ChiNameAbbr'] ?></a>
                            </td>
                            <!--                            --><?php //else:?>
                            <!--                            <td class="jjName"><a href="">-->
                            <?php //echo "--"?><!--</a></td>-->
                            <!--                            --><?php //endif;?>
                            <td><?php echo $v['FundType'] ?></td>
                            <td><?php echo $v['UnitNV'] ?></td>
                            <td><?php echo $v['AccumulatedUnitNV'] ?></td>
                            <td><?php echo $v['EndDate'] ?></td>
                            <!--                        <td>--><?php //echo $v['EndDate']?><!--</td>-->

                            <?php if ($v['RRInThreeMonth'] == '--'): ?>
                                <td><?php echo $v['RRInThreeMonth'] ?></td>
                            <?php elseif ($v['RRInThreeMonth'] > 0): ?>
                                <td class="font-red"><?php echo $v['RRInThreeMonth'] ?></td>
                            <?php else: ?>
                                <td class="font-green"><?php echo $v['RRInThreeMonth'] ?></td>
                            <?php endif; ?>

                            <?php if ($v['RRInSixMonth'] == '--'): ?>
                                <td><?php echo $v['RRInSixMonth'] ?></td>
                            <?php elseif ($v['RRInSixMonth'] > 0): ?>
                                <td class="font-red"><?php echo $v['RRInSixMonth'] ?></td>
                            <?php else: ?>
                                <td class="font-green"><?php echo $v['RRInSixMonth'] ?></td>
                            <?php endif; ?>

                            <?php if ($v['RRInSingleYear'] == '--'): ?>
                                <td><?php echo $v['RRInSingleYear'] ?></td>
                            <?php elseif ($v['RRInSingleYear'] > 0): ?>
                                <td class="font-red"><?php echo $v['RRInSingleYear'] ?></td>
                            <?php else: ?>
                                <td class="font-green"><?php echo $v['RRInSingleYear'] ?></td>
                            <?php endif; ?>


                            <td><?php echo $v['zdfl'] ?></td>
                            <td>
                                <?php if ($v['buy'] == 1): ?>
                                    <a target="_blank" href="https://trade.buyfunds.cn/trade/fundtrade.html?fundcode=<?php echo $v['SecurityCode'] ?>" class="buyIt">购买</a>
                                <?php else: ?>
                                    <a href="javascript:;" class="banBuy">购买</a>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
                <?php if (empty($list)): ?>
                    <div style="font-size: 18px;margin-top: 10px;text-align: center ;color:red;">未搜索到满足条件基金</div>
                <?php endif; ?>
                <div class="paginationJJSXa">
                    <div class="paginationBox">
                        <?php if($total > 0) echo $this->page->create_links(); ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="ad"><a href="#"><img src="<?php echo base_url(); ?>public/img/ad.png" alt="ad"></a></div>
</div>
<!--main-info End-->
<!--Main End-->
<!--selected Start-->
<div class="selectedJJ" style="display:none">
    <p class="sTitle">您已选择的基金</p>
    <ul>
        <!--<li>1<i></i></li>-->
        <!--<li>13<i></i></li>-->
        <!--<li>144<i></i></li>-->
    </ul>
    <div class="btnZone">
        <a href="javascript:;">对比</a>
        <a href="javascript:;">加自选</a>
    </div>
    <p class="sBo">
        <a href="javascript:;" class="aHide">隐藏</a>
        <a href="javascript:;" class="deleteAll">清空</a>
    </p>
</div>
<!--selected End-->
<!--returnTop Start-->
<div class="top">
    <img src="<?php echo base_url(); ?>public/img/top.png" alt="">
</div>
<!--弹出层 Start-->
<div class="popup">
    <p>操作的基金不能超过6个</p>
    <a href="javascript:;" class="closePopup">确认</a>
    <b class="closePopup"></b>
</div>
<!--弹出层 End-->
<script src="<?php echo base_url(); ?>public/js/jquery.min.js"></script>
<script src="http://hs.cnfol.com/f=Cm/Js/Base.js,ua/js/Clouds/Tables.js,Cm/Js/Checkbox.js,Cm/Js/Dialog.js,Cm/Js/Forms.js,ua/js/Clouds/Calendar.js,Cm/Js/ShowInfo.js,ui/Js/Kik/Compare.js,ui/Js/Select/Select.js"
        type="text/javascript">
</script>
<script src="<?php echo base_url(); ?>public/js/common.js"></script>
<script type="text/javascript">
    function Add1(obj){
        if(Islogin==0){
            Dialog('DiaBg','DiaAlt1');
            $('#DiaTxt1').html('登录后才能关注基金');
            return;
        }
        var jydm = $(obj).parent().parent().find('td:eq(2) a').html();
        if ($(obj).hasClass('active')){
            $.ajax({/*ajax的部分程序补充*/
                url:"/user/delmychoice?code="+jydm,//url
                type:'get',
                success:function(data){
                    $(obj).removeClass('active');
                }
            });
        }else {
            $.ajax({/*ajax的部分程序补充*/
                url:"/user/addmychoice?code="+jydm,//url
                type:'get',
                success:function(data){
                    $(obj).addClass('active');
                }
            });
        }
    }
    // ContrastBoxFollowing();
    //changeAddBg();
    if (Islogin==1)follow_init();
    showCompanyName();
    $(".closePopup").click(function () {
        $(".fade").hide();
        $(".popup").hide();
    });
    $(".miBCon").find(".filterXFJJ").find(".noMargin").addClass("active");
    $(".condition").find("a").click(function () {
        $(this).addClass("active").siblings().removeClass("active");
    });
    $(".choose").find(".unlimited").addClass("active");
    $(".jjCompany").find("a").click(function () {
        $(this).addClass("active");
        if ($(this).hasClass("active")) {
            $(".jjCompany").find("a").not(this).removeClass("active")
        }
    });
    //加载更多公司 Start
    function showCompanyName() {
        var target = true;
        $(".jjCompany").find(".showMore").click(function () {
            if (target) {
                $(".hideJJ").show();
                $(".showMore").html("收起").addClass("active");
                target = false;
            } else {
                $(".hideJJ").hide();
                $(".showMore").html("更多").removeClass("active");
                target = true;
            }
        });
    }
    //加载更多公司 End

    $(".jjType").find("a").not(".unlimited").click(function () {
        var _this = $(this);
        /*if(_this.attr('val') == 'kfsqb') {
            $("#jjType").remove();
            return false;
        }*/
        var obj = document.getElementById("jjType");
        if (!obj) {
            var typeA = document.createElement("a");
            typeA.href = "javascript:;";
            typeA.id = "jjType";
            $(".choosed").append(typeA);
            startBtnBg();
        }
        $("#jjType").text($(this).text()).click(function () {
            $(_this).removeClass("active").siblings(".unlimited").addClass("active");
            $(this).remove();
            startBtnBg();
        });
        $(".jjType").find("a").click(function () {
            if ($(_this).siblings(".unlimited").hasClass("active")) {
                $("#jjType").remove();
            }
            startBtnBg();
        })
    });
    $(".aYear").find("a").not(".unlimited").click(function () {
        var _this = $(this);
        /*if(!_this.attr('val')) {
            $("#aYear").remove();
            return false;
        }*/
        var obj = document.getElementById("aYear");
        if (!obj) {
            var typeAYear = document.createElement("a");
            typeAYear.href = "javascript:;";
            typeAYear.id = "aYear";
            $(".choosed").append(typeAYear);
            startBtnBg();
        }
        $("#aYear").text($(this).text()).click(function () {
            $(_this).removeClass("active").siblings(".unlimited").addClass("active");
            $(this).remove();
            startBtnBg();
        });
        $(".aYear").find("a").click(function () {
            if ($(_this).siblings(".unlimited").hasClass("active")) {
                $("#aYear").remove();
            }
            startBtnBg();
        })
    });
    $(".jjValue").find("a").not(".unlimited").click(function () {
        var _this = $(this);
        /*if(!_this.attr('val')) {
            $("#jjValue").remove();
            return false;
        }*/
        var obj = document.getElementById("jjValue");
        if (!obj) {
            var typeAValue = document.createElement("a");
            typeAValue.href = "javascript:;";
            typeAValue.id = "jjValue";
            $(".choosed").append(typeAValue);
            startBtnBg();
        }
        $("#jjValue").text($(this).text()).click(function () {
            $(_this).removeClass("active").siblings(".unlimited").addClass("active");
            $(this).remove();
            startBtnBg();
        });
        $(".jjValue").find("a").click(function () {
            if ($(_this).siblings(".unlimited").hasClass("active")) {
                $("#jjValue").remove();
            }
            startBtnBg();
        })
    });
    $(".jjCompany").find("a").not(".unlimited").click(function () {
        var _this = $(this);
        /*if(!_this.attr('val')) {
            $("#jjCompany").remove();
            return false;
        }*/
        var obj = document.getElementById("jjCompany");
        if (!obj) {
            var typeACompany = document.createElement("a");
            typeACompany.href = "javascript:;";
            typeACompany.id = "jjCompany";
            $(".choosed").append(typeACompany);
            startBtnBg();
        }
        $("#jjCompany").text($(this).text()).click(function () {
            $(_this).removeClass("active");
            $(".jjCompany").find(".unlimited").addClass("active");
            $(this).remove();
            startBtnBg();
        });
        $(".jjCompany").find("a").click(function () {
            if ($(_this).siblings(".unlimited").hasClass("active")) {
                $("#jjCompany").remove();
            }
            startBtnBg();
        })
    });
    $(function () {
        $(".jjType a.active").click();
        $(".aYear a.active").click();
        $(".jjValue a.active").click();
        $(".jjCompany a.active").click();
    })


    function startBtnBg() {

        if ($(".choosed").find("a").length > 0) {

            $(".startBtn").addClass("active")
        } else {

            $(".startBtn").removeClass("active");
        }
    }

    $(".startBtn").click(function () {
        if ($(this).hasClass("active")) {
            var type = $('.jjType').children('a.active').attr('val') ? $('.jjType').children('a.active').attr('val') : '';
            var companyname = $('.jjCompany').find('a.active').attr('val') ? $('.jjCompany').find('a.active').attr('val') : '';
            var year = $('.aYear').find('a.active').attr('val') ? $('.aYear').find('a.active').attr('val') : '';
            var jjj = $('.jjValue').find('a.active').attr('val') ? $('.jjValue').find('a.active').attr('val') : '';


//            alert("<?php //echo base_url();?>//fundscreen/showscreen/"+type+'/'+company+'/'+companyname);return false;
//            var company = $('.hideJJ').find('a.active').attr('val');
            var url = "<?php echo base_url();?>fundscreen/index";
            if (type)
                url += "/type/" + type;
            if (year)
                url += '/performance/' + year;
            if (companyname)
                url += '/company/' + companyname;
            if (jjj)
                url += '/net_value/' + jjj;
            window.location.href = url;
        } else {
            alert("请选择筛选条件")
        }
    });


</script>

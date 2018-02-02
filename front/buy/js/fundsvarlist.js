define(['jquery','funds'],function($){
	var n = function(e, t, n) {
        this.initialize(e, t, n)
    };
    return n.prototype ={
		$C: function(e) {
            return document.createElement(e)
        },
		trim: function(e) {
            return e.replace(/(^\s*)|(\s*$)/g, "")
        },
		CharFilter: function() {
            var e = !1,
            t = ["A", "B", "C", "D", "E", "F", "G", "H", "I", "J", "K", "L", "M", "N", "O", "P", "Q", "R", "S", "T", "U", "V", "W", "X", "Y", "Z"];
            if (glo_fundlist && glo_fundlist.length > 0) for (var n = 0; n < t.length; n++) {
                for (var r = 0; r < glo_fundlist.length; r++) if (glo_fundlist[r][1].substr(0, 1).toUpperCase() == t[n]) {
                    e = !0;
                    break
                }
                e || (t.splice(n, 1), n--),
                e = !1
            } else t = [];
            return t
        },
		CreateBox: function() {
			this.cbox = $('#fund_suggest')[0];
            //this.cbox = this.$C("dl"),
            //this.cbox.setAttribute("id", "fund_suggest"),
            //this.cbox.setAttribute("class", "fund_suggest");

            var e = this.$C("dt"),
            t = this.$C("a");
            t.setAttribute("class", "CM"),
            t.setAttribute("href", "javascript:;");
            t.innerHTML = "当前搜索";

            var n = this.$C("dd")
            n.setAttribute("class", "Inp"),
            n.innerHTML = "<p>请在搜索框内输入“<span>代码</span>”“<span>拼音</span>”或“<span>简称</span>”</p>";

            e.appendChild(t),
            this._tabindex = 0,
            this.cbox.appendChild(e),
            this.cbox.appendChild(n);
			this.range = this.CharFilter();
            var r = this;
            for (var i = 0; i < this.range.length; i++) {
                t = this.$C("a"),
                t.m = i + 1,
                t.innerHTML = this.range[i],
                e.appendChild(t);
                n = this.$C("dd");
				//n.class = "Cf";
				n.setAttribute("class", "Cf");
                var s = !1,
                u = '<a href="javascript:;">';
				this._cols = 5,
                a = this._cols;
                _c = 0,
                _cx = 0,
                _cy = 0;
				var h = '';
                for (var f = 0; f < glo_fundlist.length; f++) if (glo_fundlist[f][1].substr(0, 1).toUpperCase() == this.range[i]) {
					h += '<a data-code="'+glo_fundlist[f][0]+'" href="javascript:;">'+glo_fundlist[f][2]+'</a>';
                }
				n.innerHTML = h;
                this.cbox.appendChild(n);
            }
			
            var h = this.$C("span");
            h.innerHTML = "[关闭]",
            h.parent = this.cbox;
            e.appendChild(h);
			////return this.cbox;				
            //document.body.insertBefore(this.cbox, document.body.firstChild);
        },
		initialize: function(e, t, n) {
            this.cbox = null,
            this.text = !n || !n.text ? "请输入基金代码、名称或简拼": n.text,
            this.head = !n || !n.head ? ["选项", "代码", "类型", "简称", "支持业务"] : n.head,
            this.range = this.CharFilter() || ["A", "B", "C", "D", "E", "F", "G", "H", "I", "J", "K", "L", "M", "N", "O", "P", "Q", "R", "S", "T", "U", "V", "W", "X", "Y", "Z"],
            this.css = "fund_suggest_style",
            this.lis = null,
            this.divs = null,
            this.spans = [],
            this.result = null
        }
	};
});
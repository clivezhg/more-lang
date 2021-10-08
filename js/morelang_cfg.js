(function($) { $(document).ready( function() {


var cflags = ['ad', 'ae', 'af', 'ag', 'ai', 'al', 'am', 'an', 'ao', 'ar', 'as', 'at', 'au', 'aw', 'ax', 'az', 'ba', 'bb', 'bd', 'be',
	'bf', 'bg', 'bh', 'bi', 'bj', 'bm', 'bn', 'bo', 'br', 'bs', 'bt', 'bv', 'bw', 'by', 'bz', 'ca', 'cd', 'cf', 'cg',
	'ch', 'ci', 'ck', 'cl', 'cm', 'cn', 'co', 'cr', 'cs', 'cu', 'cv', 'cx', 'cy', 'cz', 'de', 'dj', 'dk', 'dm', 'do', 'dz',
	'ec', 'ee', 'eg', 'eh', 'er', 'es', 'et', 'fi', 'fj', 'fk', 'fm', 'fo', 'fr', 'ga', 'gb', 'gd', 'ge', 'gf', 'gh', 'gi',
	'gl', 'gm', 'gn', 'gp', 'gq', 'gr', 'gt', 'gu', 'gw', 'gy', 'hk', 'hn', 'hr', 'ht', 'hu', 'id', 'ie', 'il',
	'in', 'io', 'iq', 'ir', 'is', 'it', 'jm', 'jo', 'jp', 'ke', 'kg', 'kh', 'ki', 'km', 'kn', 'kp', 'kr', 'kw', 'ky', 'kz',
	'la', 'lb', 'lc', 'li', 'lk', 'lr', 'ls', 'lt', 'lu', 'lv', 'ly', 'ma', 'mc', 'md', 'me', 'mg', 'mh', 'mk', 'ml', 'mm',
	'mn', 'mo', 'mp', 'mq', 'mr', 'ms', 'mt', 'mu', 'mv', 'mw', 'mx', 'my', 'mz', 'na', 'nc', 'ne', 'nf', 'ng', 'ni', 'nl',
	'no', 'np', 'nr', 'nu', 'nz', 'om', 'pa', 'pe', 'pf', 'pg', 'ph', 'pk', 'pl', 'pm', 'pn', 'pr', 'ps', 'pt', 'pw', 'py',
	'qa', 're', 'ro', 'rs', 'ru', 'rw', 'sa', 'sb', 'sc', 'sd', 'se', 'sg', 'sh', 'si', 'sj', 'sk', 'sl', 'sm', 'sn', 'so',
	'sr', 'st', 'sv', 'sy', 'sz', 'tc', 'td', 'tf', 'tg', 'th', 'tj', 'tk', 'tl', 'tm', 'tn', 'to', 'tr', 'tt', 'tv', 'tw',
	'tz', 'ua', 'ug', 'um', 'us', 'uy', 'uz', 'va', 'vc', 've', 'vg', 'vi', 'vn', 'vu', 'wf', 'ws', 'ye', 'yt', 'za', 'zm', 'zw'];
/* 'CoR': Country or Region */
var locales = [
	{locale: "en_US", name: "English", CoR: "United States", flag: "us.png", date_format: "F j, Y", time_format: "g:i a"},
	{locale: "en_GB", name: "English", CoR: "United Kingdom", flag: "gb.png", date_format: "F j, Y", time_format: "g:i a"},
	{locale: "ru_RU", name: "Русский", CoR: "Russia", flag: "ru.png", date_format: "", time_format: ""},
	{locale: "ja_JP", name: "日本語", CoR: "Japan", flag: "jp.png", date_format: "", time_format: ""},
	{locale: "de_DE", name: "Deutsch", CoR: "Germany", flag: "de.png", date_format: "", time_format: ""},
	{locale: "es_ES", name: "Español", CoR: "Spain", flag: "es.png", date_format: "", time_format: ""},
	{locale: "fr_FR", name: "Français", CoR: "France", flag: "fr.png", date_format: "", time_format: ""},
	{locale: "pt_BR", name: "Português", CoR: "Brazil", flag: "br.png", date_format: "", time_format: ""},
	{locale: "it_IT", name: "Italiano", CoR: "Italy", flag: "it.png", date_format: "", time_format: ""},
	{locale: "zh_CN", name: "中文", CoR: "China", flag: "cn.png", date_format: "Y年m月d日", time_format: "H:i"},
	{locale: "hi_IN", name: "हिन्दी", CoR: "India", flag: "in.png", date_format: "", time_format: ""},
	{locale: "pl_PL", name: "Polski", CoR: "Poland", flag: "pl.png", date_format: "", time_format: ""},
	{locale: "tr_TR", name: "Türkçe", CoR: "Turkey", flag: "tr.png", date_format: "", time_format: ""},
	{locale: "fa_IR", name: "فارسی", CoR: "Iran", flag: "ir.png", date_format: "", time_format: ""},
	{locale: "nl_NL", name: "Nederlands", CoR: "Netherlands", flag: "nl.png", date_format: "", time_format: ""},
	{locale: "ko_KR", name: "한국어", CoR: "South Korea", flag: "kr.png", date_format: "", time_format: ""},
	{locale: "cs_CZ", name: "Čeština", CoR: "Czech Republic", flag: "cz.png", date_format: "", time_format: ""},
	{locale: "ar_EG", name: "العربية", CoR: "Egypt", flag: "eg.png", date_format: "", time_format: "", isRTL: true},
	{locale: "ar_SA", name: "العربية", CoR: "Saudi Arabia", flag: "sa.png", date_format: "", time_format: "", isRTL: true},
	{locale: "vi_VN", name: "Tiếng Việt", CoR: "Vietnam", flag: "vn.png", date_format: "", time_format: ""},
	{locale: "in_ID", name: "Bahasa Indonesia", CoR: "Indonesia", flag: "id.png", date_format: "", time_format: ""},
	{locale: "el_GR", name: "Ελληνικά", CoR: "Greece", flag: "gr.png", date_format: "", time_format: ""},
	{locale: "sv_SE", name: "Svenska", CoR: "Sweden", flag: "se.png", date_format: "", time_format: ""},
	{locale: "ro_RO", name: "Română", CoR: "Romania", flag: "ro.png", date_format: "", time_format: ""},
	{locale: "hu_HU", name: "Magyar", CoR: "Hungary", flag: "hu.png", date_format: "", time_format: ""},
	{locale: "da_DK", name: "Dansk", CoR: "Denmark", flag: "dk.png", date_format: "", time_format: ""},
	{locale: "th_TH", name: "ไทย", CoR: "Thailand", flag: "th.png", date_format: "", time_format: ""},
	{locale: "sk_SK", name: "Slovenčina", CoR: "Slovakia", flag: "sk.png", date_format: "", time_format: ""},
	{locale: "fi_FI", name: "Suomi", CoR: "Finland", flag: "fi.png", date_format: "", time_format: ""},
	{locale: "bg_BG", name: "Български", CoR: "Bulgaria", flag: "bg.png", date_format: "", time_format: ""},
	{locale: "he_IL", name: "עברית", CoR: "Israel", flag: "il.png", date_format: "", time_format: "", isRTL: true},
	{locale: "lt_LT", name: "Lietuvių", CoR: "Lithuania", flag: "lt.png", date_format: "", time_format: ""},
	{locale: "no_NO", name: "Norsk", CoR: "Norway", flag: "no.png", date_format: "", time_format: ""},
	{locale: "uk_UA", name: "Українська", CoR: "Ukraine", flag: "ua.png", date_format: "", time_format: ""},
	{locale: "hr_HR", name: "Hrvatski", CoR: "Croatia", flag: "hr.png", date_format: "", time_format: ""},
	{locale: "sr_RS", name: "Српски", CoR: "Serbia", flag: "rs.png", date_format: "", time_format: ""},
	{locale: "sl_SI", name: "Slovenščina", CoR: "Slovenia", flag: "si.png", date_format: "", time_format: ""},
	{locale: "lv_LV", name: "Latviešu", CoR: "Latvia", flag: "lv.png", date_format: "", time_format: ""},
	{locale: "et_EE", name: "Eesti", CoR: "Estonia", flag: "ee.png", date_format: "", time_format: ""},
	{locale: "zh_TW", name: "漢語", CoR: "Taiwan", flag: "tw.png", date_format: "", time_format: ""},
	{locale: "zh_HK", name: "漢語", CoR: "Hong Kong", flag: "hk.png", date_format: "", time_format: ""},
	{locale: "kk_KZ", name: "Қазақ", CoR: "Kazakhstan", flag: "kz.png", date_format: "", time_format: ""},
	{locale: "pt_PT", name: "Português", CoR: "Portugal", flag: "pt.png", date_format: "", time_format: ""},
	{locale: "es_MX", name: "Español", CoR: "Mexico", flag: "mx.png", date_format: "", time_format: ""},
	{locale: "es_AR", name: "Español", CoR: "Argentina", flag: "ar.png", date_format: "", time_format: ""},
	{locale: "es_CO", name: "Español", CoR: "Colombia", flag: "co.png", date_format: "", time_format: ""},
	{locale: "es_CL", name: "Español", CoR: "Chile", flag: "cl.png", date_format: "", time_format: ""},
	{locale: "es_PE", name: "Español", CoR: "Peru", flag: "pe.png", date_format: "", time_format: ""},
	{locale: "en_AU", name: "English", CoR: "Australia", flag: "au.png", date_format: "F j, Y", time_format: "g:i a"},
	{locale: "en_CA", name: "English", CoR: "Canada", flag: "ca.png", date_format: "F j, Y", time_format: "g:i a"},
	{locale: "en_IN", name: "English", CoR: "India", flag: "in.png", date_format: "", time_format: ""},
	{locale: "en_ZA", name: "English", CoR: "South Africa", flag: "za.png", date_format: "F j, Y", time_format: "g:i a"},
	{locale: "en_NZ", name: "English", CoR: "New Zealand", flag: "nz.png", date_format: "F j, Y", time_format: "g:i a"},
	{locale: "en_IE", name: "English", CoR: "Ireland", flag: "ie.png", date_format: "F j, Y", time_format: "g:i a"},
	{locale: "en_PH", name: "English", CoR: "Philippines", flag: "ph.png", date_format: "F j, Y", time_format: "g:i a"},
	{locale: "de_AT", name: "Deutsch", CoR: "Austria", flag: "at.png", date_format: "", time_format: ""},
	{locale: "es_VE", name: "Español", CoR: "Venezuela", flag: "ve.png", date_format: "", time_format: ""},
	{locale: "ar_AE", name: "العربية", CoR: "United Arab Emirates", flag: "ae.png", date_format: "", time_format: "", isRTL: true},
	{locale: "ar_MA", name: "العربية", CoR: "Morocco", flag: "ma.png", date_format: "", time_format: "", isRTL: true},
	{locale: "ar_IQ", name: "العربية", CoR: "Iraq", flag: "iq.png", date_format: "", time_format: "", isRTL: true},
	{locale: "ar_DZ", name: "العربية", CoR: "Algeria", flag: "dz.png", date_format: "", time_format: "", isRTL: true},
	{locale: "ar_QA", name: "العربية", CoR: "Qatar", flag: "qa.png", date_format: "", time_format: "", isRTL: true},
	{locale: "ar_KW", name: "العربية", CoR: "Kuwait", flag: "kw.png", date_format: "", time_format: "", isRTL: true},
	{locale: "ar_OM", name: "العربية", CoR: "Oman", flag: "om.png", date_format: "", time_format: "", isRTL: true},
	{locale: "ar_LB", name: "العربية", CoR: "Lebanon", flag: "lb.png", date_format: "", time_format: "", isRTL: true},
	{locale: "ar_LY", name: "العربية", CoR: "Libya", flag: "ly.png", date_format: "", time_format: "", isRTL: true},
	{locale: "ar_JO", name: "العربية", CoR: "Jordan", flag: "jo.png", date_format: "", time_format: "", isRTL: true},
	{locale: "ar_BH", name: "العربية", CoR: "Bahrain", flag: "bh.png", date_format: "", time_format: "", isRTL: true},
	{locale: "ar_TN", name: "العربية", CoR: "Tunisia", flag: "tn.png", date_format: "", time_format: "", isRTL: true},
	{locale: "bn_BD", name: "বাংলা", CoR: "Bangladesh", flag: "bd.png", date_format: "", time_format: ""},
	{locale: "ur_PK", name: "اردو", CoR: "Pakistan", flag: "pk.png", date_format: "", time_format: "", isRTL: true},
	{locale: "ha_NG", name: "Hausa", CoR: "Nigeria", flag: "ng.png", date_format: "", time_format: ""},
	{locale: "zh_SG", name: "漢語", CoR: "Singapore", flag: "sg.png", date_format: "", time_format: ""},
	{locale: "zh_MO", name: "漢語", CoR: "Macau", flag: "mo.png", date_format: "", time_format: ""},
	{locale: "ms_MY", name: "Bahasa Melayu", CoR: "Malaysia", flag: "my.png", date_format: "", time_format: ""},
	{locale: "de_CH", name: "Deutsch", CoR: "Switzerland", flag: "ch.png", date_format: "", time_format: ""},
	{locale: "nl_BE", name: "Nederlands", CoR: "Belgium", flag: "be.png", date_format: "", time_format: ""},
	{locale: "fr_BE", name: "Français", CoR: "Belgium", flag: "be.png", date_format: "", time_format: ""},
	{locale: "es_EC", name: "Español", CoR: "Ecuador", flag: "ec.png", date_format: "", time_format: ""},
	{locale: "es_PR", name: "Español", CoR: "Puerto Rico", flag: "pr.png", date_format: "", time_format: ""},
	{locale: "es_DO", name: "Español", CoR: "Dominican", flag: "do.png", date_format: "", time_format: ""},
	{locale: "es_GT", name: "Español", CoR: "Guatemala", flag: "gt.png", date_format: "", time_format: ""},
	{locale: "es_CR", name: "Español", CoR: "Costa Rica", flag: "cr.png", date_format: "", time_format: ""},
	{locale: "es_UY", name: "Español", CoR: "Uruguay", flag: "uy.png", date_format: "", time_format: ""},
	{locale: "es_PA", name: "Español", CoR: "Panama", flag: "pa.png", date_format: "", time_format: ""},
	{locale: "pt_AO", name: "Português", CoR: "Angola", flag: "ao.png", date_format: "", time_format: ""},
	{locale: "ta_LK", name: "தமிழ்", CoR: "Sri Lanka", flag: "lk.png", date_format: "", time_format: ""},
	{locale: "lb_LU", name: "Lëtzebuergesch", CoR: "Luxembourg", flag: "lu.png", date_format: "", time_format: ""},
	{locale: "my_MM", name: "ဗမာစာ", CoR: "Myanmar", flag: "mm.png", date_format: "", time_format: ""},
	{locale: "tuk", name: "Türkmençe", CoR: "Turkmenistan", flag: "tm.png", date_format: "", time_format: ""},
	{locale: "uz_UZ", name: "O'zbek", CoR: "Uzbekistan", flag: "uz.png", date_format: "", time_format: ""},
	{locale: "bel", name: "Беларуская мова", CoR: "Belarus", flag: "by.png", date_format: "", time_format: ""},
	{locale: "az", name: "Azərbaycan dili", CoR: "Azerbaijan", flag: "az.png", date_format: "", time_format: ""},
	{locale: "ne_NP", name: "नेपाली", CoR: "Nepal", flag: "np.png", date_format: "", time_format: ""},
	{locale: "sw_KE", name: "Kiswahili", CoR: "Kenya", flag: "ke.png", date_format: "", time_format: ""},
	{locale: "sw_TZ", name: "Kiswahili", CoR: "Tanzania", flag: "tz.png", date_format: "", time_format: ""},
	{locale: "sw_UG", name: "Kiswahili", CoR: "Uganda", flag: "ug.png", date_format: "", time_format: ""},
	{locale: "am_ET", name: "አማርኛ", CoR: "Ethiopia", flag: "et.png", date_format: "", time_format: ""},
	{locale: "fr_CD", name: "Français", CoR: "Dr Congo", flag: "cd.png", date_format: "", time_format: ""},
	{locale: "fr_CI", name: "Français", CoR: "Ivory Coast", flag: "ci.png", date_format: "", time_format: ""},
	{locale: "fr_CM", name: "Français", CoR: "Cameroon", flag: "cm.png", date_format: "", time_format: ""},
	{locale: "quz_BO", name: "Runasimi", CoR: "Bolivia", flag: "bo.png", date_format: "", time_format: ""},
	{locale: "gn_PY", name: "Avañe'ẽ", CoR: "Paraguay", flag: "py.png", date_format: "", time_format: ""},
	{locale: "is_IS", name: "Íslenska", CoR: "Iceland", flag: "is.png", date_format: "", time_format: ""},
	{locale: "km_KH", name: "ភាសាខ្មែរ", CoR: "Cambodia", flag: "kh.png", date_format: "", time_format: ""},
	{locale: "ar_YE", name: "العربية", CoR: "Yemen", flag: "ye.png", date_format: "", time_format: "", isRTL: true},
	{locale: "es_SV", name: "Español", CoR: "El Salvador", flag: "sv.png", date_format: "", time_format: ""},
	{locale: "es_HN", name: "Español", CoR: "Honduras", flag: "hn.png", date_format: "", time_format: ""},
	{locale: "wol", name: "Wolof", CoR: "Senegal", flag: "sn.png", date_format: "", time_format: ""},
	{locale: "el_CY", name: "Ελληνικά", CoR: "Cyprus", flag: "cy.png", date_format: "", time_format: ""},
	{locale: "sna", name: "ChiShona", CoR: "Zimbabwe", flag: "zw.png", date_format: "", time_format: ""},
	{locale: "en_ZM", name: "English", CoR: "Zambia", flag: "zm.png", date_format: "", time_format: ""},
	{locale: "en_TT", name: "English", CoR: "Trinidad", flag: "tt.png", date_format: "", time_format: ""},
	{locale: "en_PG", name: "English", CoR: "Papua New Guinea", flag: "pg.png", date_format: "", time_format: ""},
	{locale: "en_BW", name: "English", CoR: "Botswana", flag: "bw.png", date_format: "", time_format: ""},
	{locale: "en_JM", name: "English", CoR: "Jamaica", flag: "jm.png", date_format: "", time_format: ""},
	{locale: "lo", name: "ພາສາລາວ", CoR: "Laos", flag: "la.png", date_format: "", time_format: ""},
	{locale: "bs_BA", name: "Bosanski", CoR: "Bosnia ...", flag: "ba.png", date_format: "", time_format: ""},
	{locale: "ps", name: "پښتو", CoR: "Afghanistan", flag: "af.png", date_format: "", time_format: ""},
	{locale: "ka_GE", name: "ქართული", CoR: "Georgia", flag: "ge.png", date_format: "", time_format: ""},
	{locale: "fr_ML", name: "Français", CoR: "Mali", flag: "ml.png", date_format: "", time_format: ""},
	{locale: "fr_GA", name: "Français", CoR: "Gabon", flag: "ga.png", date_format: "", time_format: ""},
	{locale: "fr_BF", name: "Français", CoR: "Burkina Faso", flag: "bf.png", date_format: "", time_format: ""},
	{locale: "sq", name: "Shqip", CoR: "Albania", flag: "al.png", date_format: "", time_format: ""},
	{locale: "mlt", name: "Malti", CoR: "Malta", flag: "mt.png", date_format: "", time_format: ""},
	{locale: "pt_MZ", name: "Português", CoR: "Mozambique", flag: "mz.png", date_format: "", time_format: ""},
	{locale: "mfe", name: "Kreol Morisien", CoR: "Mauritius", flag: "mu.png", date_format: "", time_format: ""},
	{locale: "mn", name: "Монгол", CoR: "Mongolia", flag: "mn.png", date_format: "", time_format: ""},
	{locale: "af", name: "Afrikaans", CoR: "Namibia", flag: "na.png", date_format: "", time_format: ""},
	{locale: "ms_BN", name: "Bahasa Melayu", CoR: "Brunei", flag: "bn.png", date_format: "", time_format: ""},
	{locale: "hy", name: "Հայերեն", CoR: "Armenia", flag: "am.png", date_format: "", time_format: ""},
	{locale: "en_BS", name: "English", CoR: "Bahamas", flag: "bs.png", date_format: "", time_format: ""},
	{locale: "mk_MK", name: "Македонски јазик", CoR: "Macedonia", flag: "mk.png", date_format: "", time_format: ""},
	{locale: "fr_GN", name: "Français", CoR: "Guinea", flag: "gn.png", date_format: "", time_format: ""},
	{locale: "fr_TD", name: "Français", CoR: "Chad", flag: "td.png", date_format: "", time_format: ""},
	{locale: "mg_MG", name: "Malagasy", CoR: "Madagascar", flag: "mg.png", date_format: "", time_format: ""},
	{locale: "ro_MO", name: "Română", CoR: "Moldova", flag: "mo.png", date_format: "", time_format: ""},
	{locale: "es_NI", name: "Español", CoR: "Nicaragua", flag: "ni.png", date_format: "", time_format: ""},
]; // https://en.wikipedia.org/wiki/Languages_used_on_the_Internet // Not follow after the "Eesti"
for (var i = 0; i < locales.length; i++) {
	var locale = locales[i];
	locale.label = locale.name;
	if (! locale.date_format) locale.date_format = "Y-m-d";
	if (! locale.time_format) locale.time_format = "H:i";
}
var PART_CNT = 50; // The number of locales for each section in display.

var flagBase = (typeof ml_plugin_url !== "undefined") ? ml_plugin_url+"cflag/" : "../wp-content/plugins/more-lang/cflag/";
var ML_CFG_CHANGE = "ml_cfg_change";

if ( $('#ml-langcfg-tbl').length > 0 ) {
	var $tblBody = $('#ml-langcfg-tbl tbody#ml-langcfg-tbody');
	var inputEle = "<input type='text' class='ml-grid-input'>";
	function addLocaleRow(locale, name, label, flag, date_format, time_format, moreOpt) {
		var $newline = $("<tr>").appendTo($tblBody);
		$(inputEle).val(locale).appendTo("<td>").parent().appendTo($newline);
		$(inputEle).val(name).appendTo("<td>").parent().appendTo($newline);
		$(inputEle).val(label).appendTo("<td>").parent().appendTo($newline);
		var $flagTd = $("<td>");
		fillFlagTd($flagTd, flag);
		$flagTd.appendTo($newline);
		$(inputEle).val(date_format).appendTo("<td>").parent().appendTo($newline);
		$(inputEle).val(time_format).appendTo("<td>").parent().appendTo($newline);
		var $moreTd = $("<td>");
		fillMoreTd($moreTd, moreOpt);
		$moreTd.appendTo($newline);
		$tblBody.trigger( ML_CFG_CHANGE );
		$("#ml-up-lang-temp").clone().removeAttr("id").css("display", "").appendTo("<td>").parent().appendTo($newline).end().end().on("click", function() {
			var $theTr = $(this).parent().parent();
			$theTr.prev().before( $theTr );
			$tblBody.trigger( ML_CFG_CHANGE );
		});
		$("#ml-del-lang-temp").clone().removeAttr("id").css("display", "").appendTo($newline.find("td:last-child")).parent().end().end().on("click", function() {
			$newline.remove();
			refreshState(); // if the button is removed, the delegate event '$("#ml-langcfg-tbl").on("click", "button", ...);' will not get fired
			$tblBody.trigger( ML_CFG_CHANGE );
		});
	}

	function fillFlagTd($flagTd, flag) {
		flag = flag || "";
		$sel = $('<select></select>');
		var valExist = false;
		for (var i = 0; i < cflags.length; i++) {
			$opt = $('<option></option>').appendTo($sel);
			var optVal = cflags[i] + ".png";
			$opt.val(optVal);
			$opt.text(optVal);
			if (flag === optVal) valExist = true;
		}
		if (!valExist && flag) $('<option></option>').val(flag).text(flag).appendTo($sel);
		$sel.val(flag);
		$flagTd.append($sel);
		$sel.select2({templateResult: formatState_f, templateSelection: formatState_f, tags: true, tokenSeparators: [',', ' ']});
		$sel.data("select2").$container.addClass("ml-flag-container");
		$sel.data("select2").$dropdown.addClass("ml-flag-dropdown");
	}

	function fillMoreTd($moreTd, moreOpt) {
		moreOpt = moreOpt || {};
		var $tpl = $("#ml-moreopt-tpl");
		var $mo_div = $tpl.find(".ml-moreopt-wrap").eq(0).clone();
		$mo_div.find(".ml-moreopt-ind").on("click",
				function() { if ( $(".ml-moreopt-show").not($mo_div).length === 0 ) $mo_div.toggleClass("ml-moreopt-show"); });
		$mo_div.find(".ml-missing-content").val(moreOpt.missing_content);
		$mo_div.find(".ml-is-rtl").prop("checked", moreOpt.isRTL || false);
		$mo_div.appendTo($moreTd);
	}

	function formatState_f(state) {
		if (!state.element) { return state.text; }
		var $state = $('<span></span>').text( " " + state.text).prepend(
				$('<img alt="X">').attr('src', flagBase + state.element.value) );
		return $state;
	};

	$("#ml-add-locale").on("click", function() {
		for (var k = 0; k < locales.length; k++) {
			var loc = locales[k];
			if (loc.locale === $("#ml-locale-sel").val() ) {
				if ( $('#ml-langcfg-tbl tr td:first-child input').filter(function(){ return $(this).val() === loc.locale }).length === 0 ) {
					addLocaleRow(loc.locale, loc.name, loc.label, loc.flag, loc.date_format, loc.time_format, {isRTL: loc.isRTL});
					refreshState();
				}
				break;
			}
		}
	});
	$("#ml-create-locale").on("click", function() {
		addLocaleRow("", "", "", "", "", "");
	});
	$("#ml-locale-sel").on("change", function() {
		refreshState();
	});
	function refreshState() {
		var valLocSel = $("#ml-locale-sel").val();
		var toDisable = ( !valLocSel || $('#ml-langcfg-tbl tr td:first-child input').filter(function(){ return $(this).val() === valLocSel }).length > 0 );
		$("#ml-add-locale").prop("disabled", toDisable);
	}
	$("#ml-langcfg-tbl").on("change input", ":input", refreshState);
	$("#ml-langcfg-tbl").on("click", "button", refreshState);

	var ML_POS_INPUTMODE_SEL = "0"; // the same as "morelang_plugin.php"
	var ML_POS_INPUTMODE_TEXT = "1";
	$('input[name="ml-pos-inputmode"]').on("change", function() {
		var val = $(this).val();
		// Actually it will always be true in the current main browsers(only selected Radios will trigger 'change')
		var checked = $(this).prop("checked");
		if (val === ML_POS_INPUTMODE_SEL && checked || val === ML_POS_INPUTMODE_TEXT && !checked) {
			$("#ml-pos-text").prop("disabled", true);
			$("#ml-pos-sel").prop("disabled", false);
		}
		else {
			$("#ml-pos-text").prop("disabled", false).focus();
			$("#ml-pos-sel").prop("disabled", true);
		}
	});

	/* Initialize the form controls & values */
	function initForm() {
		if ( mlLangReady() ) {
			var tmpLangs = ml_registered_langs.slice(0); // shallow clone
			for ( var memb in tmpLangs ) {
				var langObj = tmpLangs[memb];
				addLocaleRow(langObj.locale, langObj.name, langObj.label, langObj.flag, langObj.date_format, langObj.time_format, langObj.moreOpt);
			}
		}
		else {
			var locObj = {locale: "", name: "", CoR: "", flag: "", date_format: "", time_format: ""};
			for (var i = 0; i < locales.length; i++) {
				if (locales[i].locale === ml_dft_locale) locObj = locales[i];
			}
			addLocaleRow(ml_dft_locale, locObj.name, locObj.label, locObj.flag, locObj.date_format, locObj.time_format);
		}

		if (typeof ml_opt_obj === "object" && ml_opt_obj != null) {
			$("#ml-auto-chooser").prop("checked", ml_opt_obj.ml_auto_chooser || false);
			if (ml_opt_obj.ml_pos_inputmode === ML_POS_INPUTMODE_TEXT) {
				$("#ml-pos-inputmode-text").prop("checked", true);
				/* "wp_kses( $value, wp_kses_allowed_html('post') )" will encode the HTML entities, like ">" */
				var posText = $("<textarea></textarea>").html( ml_opt_obj.ml_pos_text ).val();
				$("#ml-pos-text").prop("disabled", false).val( posText );
				$("#ml-pos-sel").prop("disabled", true);
			}
			else {
				$("#ml-pos-inputmode-sel").prop("checked", true);
				$("#ml-pos-sel").prop("disabled", false).val(ml_opt_obj.ml_pos_sel);
				$("#ml-pos-text").prop("disabled", true);
			}
			$("#ml-style-sel").prop("selectedIndex", 0);
			if (ml_opt_obj.ml_style_sel) $("#ml-style-sel").val(ml_opt_obj.ml_style_sel);

			$("#ml-no-css").prop("checked", ml_opt_obj.ml_no_css || false);
			$("#ml-auto-redirect").prop("checked", ml_opt_obj.ml_auto_redirect || false);
			$("input[name=ml-url-mode][value=" + ml_opt_obj.ml_url_mode + "]").prop("checked", true);
			$("#ml-url-locale-lower-case").prop("checked", ml_opt_obj.ml_url_locale_lower_case || false);
			$("#ml-url-locale-to-hyphen").prop("checked", ml_opt_obj.ml_url_locale_to_hyphen || false);
			$("#ml-url-locale-no-country").prop("checked", ml_opt_obj.ml_url_locale_no_country || false);
			$("#ml-gen-hreflang").prop("checked", ml_opt_obj.ml_gen_hreflang || false);
			$("#ml-short-label").prop("checked", ml_opt_obj.ml_short_label || false);
			$("#ml-switcher-popup").prop("checked", ml_opt_obj.ml_switcher_popup || false);
			$("#ml-clear-when-delete-plugin").prop("checked", ml_opt_obj.ml_clear_when_delete_plugin || false);
			$("#ml-not-add-posts-column").prop("checked", ml_opt_obj.ml_not_add_posts_column || false);

			$("#ml-enable-special-3party-compat").prop("checked", ml_opt_obj.ml_enable_special_3party_compat || false);
		}
	}
	initForm();


	/* Prepare the input data for submitting */
	$("#ml-langcfg-form").on("submit", function() {
		var inputVal = getInputVal( true );
		if (invalidInput) {
			if (typeof invalidMsg === "string" && invalidMsg) {
				alert( invalidMsg );
			}
			return false;
		}
		$("#morelang_option").val( inputVal );
		return true;
	});

	/* Updates the state of "submit" button when changed */
	$("#ml-langcfg-tbl").on("change input " + ML_CFG_CHANGE, "*", function(evt) {
		var inputVal = getInputVal();
		if ( JSON.stringify( window.ml_opt_obj ) !== inputVal ) {
			$("input#submit").prop("disabled", false);
		}
		else {
			$("input#submit").prop("disabled", true);			
		}
		if (evt.type === ML_CFG_CHANGE) {
			/* Create tooltip for the default row */
			var $dftTd = $("#ml-langcfg-tbl").find("tbody tr:first-child td:last-child");
			if ($dftTd.length && ! $dftTd.find("#ml-dft-help").length) {
				var $mlDftHelp = $dftTd.closest("tbody").find("#ml-dft-help");
				if (! $mlDftHelp.length) {
					$mlDftHelp = $("#ml-dft-help-temp").find(".ml-tooltip").clone();
					$mlDftHelp.attr("id", "ml-dft-help").addClass("ml-tooltip-rl");
				}
				$dftTd.append(" ").append( $mlDftHelp );
			}
		}
	});
	$("#ml-langcfg-tbl").find("thead").trigger( ML_CFG_CHANGE );

	var invalidInput = false;
	var invalidMsg = "";

	/* Gets the inputs as a JSON string */
	function getInputVal( focusInvalid ) {
		invalidInput = false;
		invalidMsg = "";
		var locObjs = [];
		var idx = 0;
		var ml_opt = {ml_registered_langs: locObjs};

		ml_opt.ml_url_locale_lower_case = $("#ml-url-locale-lower-case").prop("checked");
		ml_opt.ml_url_locale_to_hyphen = $("#ml-url-locale-to-hyphen").prop("checked");
		ml_opt.ml_url_locale_no_country = $("#ml-url-locale-no-country").prop("checked");
		$("#ml-langcfg-tbl tbody tr").each(function() {
			var locObj = {};
			var $inputs = $(this).find("input, select, textarea"); // ":input" will get unexpected elements like 'button'.
			locObj.locale = $inputs.eq(0).val().trim();
			locObj.name = $inputs.eq(1).val().trim();
			locObj.label = $inputs.eq(2).val().trim();
			locObj.flag = $inputs.eq(3).val() || "";
			locObj.date_format = $inputs.eq(4).val().trim();
			locObj.time_format = $inputs.eq(5).val().trim();
			var moreOpt = {};
			moreOpt.missing_content = $inputs.filter(".ml-missing-content").val();
			moreOpt.isRTL = $inputs.filter(".ml-is-rtl").prop("checked");
			locObj.moreOpt = moreOpt;
			if ( locObj.locale === "" ) {
				focusInvalid && $inputs.eq(0).focus();
				invalidMsg = $('#ml-opt-wrap #ml-msg-empty').text();
				invalidInput = true;
			}
			for ( var i = 0; i < locObjs.length; i++ ) {
				if ( locObj.locale === locObjs[i].locale ) {
					focusInvalid && $inputs.eq(0).focus();
					invalidMsg = $('#ml-opt-wrap #ml-msg-duplicate').text();
					invalidInput = true;
				}
			}
			/* 'url_locale' will be used in Frontend URL */
			var url_locale = locObj.locale;
			if ( ml_opt.ml_url_locale_lower_case ) {
				url_locale = url_locale.toLocaleLowerCase();
			}
			if ( ml_opt.ml_url_locale_no_country ) {
				var idx_ = url_locale.indexOf("_");
				if (idx_ >= 0) url_locale = url_locale.substr(0, idx_);
			}
			if ( ml_opt.ml_url_locale_to_hyphen ) {
				url_locale = url_locale.replace("_", "-");
			}
			locObj.url_locale = url_locale;

			locObjs[idx++] = locObj;
		});

		if ($("#ml-auto-chooser").prop("checked")) {
			ml_opt.ml_auto_chooser = true;
			ml_opt.ml_pos_inputmode = ML_POS_INPUTMODE_SEL;
			if ($("#ml-pos-inputmode-sel").prop("checked")) {
				ml_opt.ml_pos_sel = $("#ml-pos-sel").val();
			}
			else {
				ml_opt.ml_pos_inputmode = ML_POS_INPUTMODE_TEXT;
				ml_opt.ml_pos_text = $("#ml-pos-text").val();
			}
			ml_opt.ml_style_sel = $("#ml-style-sel").val();
		}
		else {
			ml_opt.ml_auto_chooser = false;
		}
		ml_opt.ml_no_css = $("#ml-no-css").prop("checked");
		ml_opt.ml_auto_redirect = $("#ml-auto-redirect").prop("checked");
		ml_opt.ml_url_mode = $("input[name=ml-url-mode]:checked").val();
		ml_opt.ml_gen_hreflang = $("#ml-gen-hreflang").prop("checked");
		ml_opt.ml_short_label = $("#ml-short-label").prop("checked");
		ml_opt.ml_switcher_popup = $("#ml-switcher-popup").prop("checked");
		ml_opt.ml_clear_when_delete_plugin = $("#ml-clear-when-delete-plugin").prop("checked");
		ml_opt.ml_not_add_posts_column = $("#ml-not-add-posts-column").prop("checked");

		ml_opt.ml_enable_special_3party_compat = $("#ml-enable-special-3party-compat").prop("checked");

		/* Interface for extension */
		$("#ml-langcfg-tbl").trigger("ml-option-get-input-val", ml_opt);
		if ( ml_opt.err_msg ) {
			if (typeof ml_opt.err_msg === "string") invalidMsg = ml_opt.err_msg;
			invalidInput = true;
		}

		return JSON.stringify( ml_opt );
	};


	/* Generate Select2 box for pre-defined locales */
	$localeSel = $("#ml-locale-sel");
	var curLocIdx = 0;
	function addPartialLocales() {
		for (var i = curLocIdx; i < curLocIdx + PART_CNT; i++) {
			if (i >= locales.length) break;
			var $opt = $("<option></option>");
			$opt.val(locales[i].locale);
			var countryOrRegion = locales[i].CoR ? (" (" + locales[i].CoR + ")") : "";
			$opt.text(locales[i].name + countryOrRegion);
			$opt.attr("data-flag", locales[i].flag);
			$localeSel.append($opt);
		}
		curLocIdx = i;
	}
	addPartialLocales();

	var s2LocInst = $localeSel.select2({placeholder: $("#ml-opt-wrap #ml-msg-prelocale").text(),
			templateResult: formatState_l, templateSelection: formatState_l});
	function formatState_l(state) {
		if (!state.element) { return state.text; }
		var $state = $('<span><span>' + state.text + '</span><img src="' + flagBase
				+ state.element.getAttribute("data-flag") + '" /></span>');
		return $state;
	};

	/* Add|remove the "More..." button on opening */
	s2LocInst.on("select2:opening", function (evt) {
		var s2data = s2LocInst.data("select2");
		s2data.$dropdown.find(".ml-more-locale").remove();
		if (curLocIdx >= locales.length) return;

		$butt = $("<button class='button button-secondary button-small'></button>");
		$butt.text( (window.ml_i18n_obj && ml_i18n_obj.more_btn_label) || "More..." );
		$("<div class='ml-more-locale'></div>").append($butt).appendTo( s2data.$dropdown.find(".select2-results") );

		$butt.on("click", function() {
			addPartialLocales();
			s2data.$dropdown.find(".select2-search__field").trigger("input"); // Will refresh the option items
			var $options = $(".select2-results .select2-results__options");
			$options.scrollTop( $options.prop("scrollHeight") );
			if (curLocIdx >= locales.length) $butt.prop("disabled", true);
		})
	});
}


} ); } )(jQuery); // end '$(document).ready'

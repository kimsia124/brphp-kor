function sl(x) {
	document.br.Command[x].checked = true;
}
function registcheck(){
	var msg = '';

	if(document.regist.f_name.value == ''){
		msg = msg + "姓が入力されていません。\n";
	}
	if(document.regist.f_name.value.length > 8){
		msg = msg + "姓の文字数がオーバーしています。（全角4文字まで）。\n";
	}
	if(document.regist.f_name.value.match(/[\x21-\x7E]+/)){
		msg = msg + "姓に漢字、ひらがな、カタカナ以外は利用できません。\n";
	}
	if(document.regist.l_name.value == ''){
		msg = msg + "名が入力されていません。\n";
	}
	if(document.regist.l_name.value.length > 8){
		msg = msg + "名の文字数がオーバーしています。（全角4文字まで）。\n";
	}
	if(document.regist.l_name.value.match(/[\x21-\x7E]+/)){
		msg = msg + "名に漢字、ひらがな、カタカナ以外は利用できません。\n";
	}
	if(document.regist.id.value == ''){
		msg = msg + "IDが入力されていません。\n";
	}
	if(document.regist.id.value.length > 12){
		msg = msg + "IDの文字数がオーバーしています(12文字以内)\n";
	}
	if(document.regist.id.value.match(/[^a-zA-Z0-9]+/)){
		msg = msg + "IDに半角英数字以外を使うことは出来ません。\n";
	}
	if(document.regist.pass.value == ''){
		msg = msg + "パスワードが入力されていません。\n";
	}
	if(document.regist.pass.value.length > 12){
		msg = msg + "パスワードの文字数がオーバーしています(12文字以内)\n";
	}
	if(document.regist.pass.value.match(/[^a-zA-Z0-9]+/)){
		msg = msg + "パスワードに半角英数字以外を使うことは出来ません。\n";
	}
	if(!document.regist.sex.value.match(/^[MF]$/)){
		msg = msg + "性別が正しく選択されていません。\n";
	}
	if(document.regist.icon.value == 'NOICON'){
		msg = msg + "アイコンが正しく選択されていません。\n";
	}

	if(msg == ''){
		return true;
	}else{
		alert(msg);
		return false;
	}
}
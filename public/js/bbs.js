document.addEventListener("DOMContentLoaded", () => {
　  const imageInput = document.getElementById("imageInput");
　  imageInput.addEventListener("change", () => {
　    if (imageInput.files.length < 1) {
　      // 未選択の場合
　      return;
　    }
　    if (imageInput.files[0].size > 5 * 1024 * 1024) {
　      // ファイルが5MBより多い場合
　      alert("5MB以下のファイルを選択してください。");
　      imageInput.value = "";
　    }
　  });
　});
　// アンカー機能
  for(const val of array){
	var org = document.getElementById(val);
	var str = org.textContent;
	var re = />>\d{1,}/g; // >>数字 を検索対象
	var mat1 = str.replace(re, "<a href='#$&'>$&</a>"); // 対象をaタグで加工
	org.innerHTML = mat1; // 中身を戻す
  }
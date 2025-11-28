document.addEventListener("click", myFunction);
function myFunction() {
 const facture= document.querySelectorAll("table");
 
 facture.forEach(tr => {
	 console.log('NB '+tr.length)
  tr.innerHTML = "#32bacf"; 
})
}
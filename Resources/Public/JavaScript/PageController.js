import AjaxRequest from "@typo3/core/ajax/ajax-request.js";

let request = new AjaxRequest(TYPO3.settings.ajaxUrls['slug_list']+'&maxentries=10&table=pages&orderby=crdate&order=ASC');
console.log('HELL YEAH')
const json = {foo: 'bar'};
let promise = request.post(json, {
  headers: {
    'Content-Type': 'application/json; charset=utf-8'
  }
});

promise.then(async function (response) {
  const responseText = await response.resolve();
  console.log(responseText);
  console.log(TYPO3.settings.ajaxUrls['slug_list'])
});

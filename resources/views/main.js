var tl = new TimelineLite();

var iconWrapper = document.getElementsByClassName('success');
var icon = document.getElementsByClassName('success__icon');
var iconBorder = document.getElementsByClassName('success__icon-border');
var iconTick = document.getElementsByClassName('success__icon-tick');

var success = true;

var title = document.getElementsByClassName('success__title');


tl.set(iconWrapper, {y: '50%'})
  .set(icon, {width: '50px', height: '50px'})
  .set(iconBorder, {drawSVG: '80%'})
  .set(title, {opacity: 0})
  .to(iconBorder, 0.8, { rotation: 360, transformOrigin: '50% 50%', ease: Linear.easeNone, repeat: -1});
 
if (success) {
	tl.to(iconWrapper, 0.5, {y: '0%', ease: Expo.easeInOut})
	  .to(icon, 0.5, {width: '100px', height: '100px', ease: Expo.easeInOut})
	  .to(iconBorder, 0.5, {drawSVG:"100%", ease: Expo.easeOut}, '-=0.5')
  	  .to(iconTick, 0.7, {drawSVG:"100%", ease: Expo.easeOut})
	  .set(iconBorder, {rotation: 0})
	  .to(title, 0.7, {opacity: 1, y: -5, ease: Expo.easeInOut}, '-=0.7');
}

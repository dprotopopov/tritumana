(function($) {

	/**
	 * Daily Counter Clock Face
	 *
	 * This class will generate a daily counter for FlipClock.js. A
	 * daily counter will track days, hours, minutes, and seconds. If
	 * the number of available digits is exceeded in the count, a new
	 * digit will be created.
	 *
	 * @param  object  The parent FlipClock.Factory object
	 * @param  object  An object of properties to override the default
	 */

	FlipClock.DailyCounterFace = FlipClock.Face.extend({

		showSeconds: true,

		/**
		 * Constructor
		 *
		 * @param  object  The parent FlipClock.Factory object
		 * @param  object  An object of properties to override the default
		 */

		constructor: function(factory, options) {
			this.base(factory, options);
		},

		/**
		 * Build the clock face
		 */

		build: function(excludeHours, time) {
			var t        = this;
			var children = this.factory.$el.find('ul');
			var lists    = [];
			var offset   = 0;

			time     = time ? time : this.factory.time.getDayCounter(this.showSeconds);

			if(time.length > children.length) {
				$.each(time, function(i, digit) {
					lists.push(t.createList(digit));
				});
			}

			this.factory.lists = lists;

			if(this.showSeconds) {
				$(this.createDivider('Seconds')).insertBefore(this.factory.lists[this.factory.lists.length - 2].$el);
			}
			else
			{
				offset = 2;
			}

			$(this.createDivider('Minutes')).insertBefore(this.factory.lists[this.factory.lists.length - 4 + offset].$el);
			$(this.createDivider('Hours')).insertBefore(this.factory.lists[this.factory.lists.length - 6 + offset].$el);
			$(this.createDivider('Days', true)).insertBefore(this.factory.lists[0].$el);

			// this._clearExcessDigits();

			if(this.autoStart) {
				this.start();
			}
		},

		/**
		 * Flip the clock face
		 */

		flip: function(time, doNotAddPlayClass) {
			if(!time) {
				time = this.factory.time.getDayCounter(this.showSeconds);
			}

			this.autoIncrement();

			this.base(time, doNotAddPlayClass);
		}

		/**
		 * Clear the excess digits from the tens columns for sec/min
		 */

		 /*
		_clearExcessDigits: function() {
			var tenSeconds = this.factory.lists[this.factory.lists.length - 2];
			var tenMinutes = this.factory.lists[this.factory.lists.length - 4];

			for(var x = 6; x < 10; x++) {
				tenSeconds.$el.find('li:last-child').remove();
				tenMinutes.$el.find('li:last-child').remove();
			}
		}
		*/

	});

}(jQuery));
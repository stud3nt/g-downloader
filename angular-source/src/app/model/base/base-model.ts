export class BaseModel {

	/**
	 * Converts class values to array
	 * @param replaceUnderscoreGetters - replace '_' chars in names of getter/setter variables
	 * @private
	 */
	public toArray(replaceUnderscoreGetters: boolean = true) {
		let array = {};

		for (let parameterName of Object.keys(this)) {
			let properParameterName = null;
			let properParameterValue = null;
			let parameterValue = <any>this[parameterName];

			if (parameterName.substr(0, 1) === '_' && replaceUnderscoreGetters)
				properParameterName = parameterName.substr(1);
			else if (!replaceUnderscoreGetters)
				properParameterName = parameterName;
			else
				continue;

			if (typeof parameterValue === 'object') {
				if (parameterValue !== null && parameterValue instanceof Array) {
					properParameterValue = [];
					let toArrayExists = false;

					for (let paramKey in parameterValue) {
						let tmpParam = parameterValue[paramKey];

						if (typeof parameterValue[paramKey]['toArray'] === 'function') {
							properParameterValue[paramKey] = tmpParam.toArray();
							toArrayExists = true;
						} else {
							properParameterValue[paramKey] = JSON.stringify(tmpParam);
						}
					}

					if (toArrayExists === false) // this IS NOT array of objects - just simple array;
						properParameterValue = JSON.stringify(parameterValue);
				} else if (parameterValue !== null && typeof parameterValue['toArray'] === 'function') {
					properParameterValue = JSON.stringify(
						parameterValue.toArray()
					);
				} else {
					properParameterValue = JSON.stringify(parameterValue);
				}
			} else {
				properParameterValue = parameterValue;
			}

			array[properParameterName] = properParameterValue;
		}

		return array;
	}
}
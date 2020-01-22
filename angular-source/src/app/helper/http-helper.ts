
export class HttpHelper {

	/**
	 * Converts array or object to form data
	 *
	 * @param object - array, object or model with parameters;
	 */
	public static convertObjectToFormData(object: any) : FormData {
		let formData = new FormData();

		if (typeof object['toArray'] === 'function') {
			let objectArray = object.toArray();

			for (let objectIndex in objectArray) {
				formData.append(objectIndex, objectArray[objectIndex]);
			}
		} else {
			if (Object.keys(object).length > 0) {
				for (let parameterName in object) {
					let parameterValue = object[parameterName];

					if (parameterValue !== null && typeof parameterValue === 'object') {
						if (typeof parameterValue['toArray'] === 'function') {
							parameterValue = parameterValue.toArray();
						} else {
							parameterValue = JSON.stringify(parameterValue);
						}
					} else if (typeof parameterValue === 'function') {
						continue;
					}

					formData.append(parameterName, parameterValue);
				}
			}
		}


		return formData;
	}

}
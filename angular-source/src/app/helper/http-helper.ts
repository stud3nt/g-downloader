
export class HttpHelper {

	/**
	 * Converts array or object to form data
	 *
	 * @param params - array, object or model with parameters;
	 */
	public static convertObjectToFormData(params: any) : FormData {
		let formData = new FormData();

		if (Object.keys(params).length > 0) {
			for (let parameterName in params) {
				let parameterValue = params[parameterName];

				if (typeof parameterValue === 'object') {
					parameterValue = JSON.stringify(parameterValue);
				} else if (typeof parameterValue === 'function') {
					continue;
				}

				formData.append(parameterName, parameterValue);
			}
		}

		return formData;
	}

}

export class HttpHelper {

	public static Array = 'output_array';
	public static Object = 'output_object';
	public static FormData = 'output_form_data';

	/**
	 * Converts array or object to form data
	 *
	 * @param object - array, object or model with parameters;
	 * @param output - output object
	 */
	//public static convertObjectToFormData(object: any, output: string = HttpHelper.OutputFormData): any {
	public static convert(object: any, output: string = HttpHelper.FormData): any {
		let convertedData = [];

		if (typeof object['toArray'] === 'function') {
			let objectArray = object.toArray();

			for (let objectIndex in objectArray) {
				convertedData[objectIndex] = objectArray[objectIndex];
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

					convertedData[parameterName] = parameterValue;
				}
			}
		}

		let outputData = null;

		if (convertedData) {
			for (let dataIndex in convertedData) {
				switch (output) {
					case HttpHelper.FormData:
						outputData = (!outputData) ? (new FormData()) : outputData;

						if (Array.isArray(convertedData[dataIndex]))
							outputData.append(dataIndex, JSON.stringify(convertedData[dataIndex]))
						else
							outputData.append(dataIndex, convertedData[dataIndex]);
						break;

					case HttpHelper.Object:
						outputData = (!outputData) ? {} : outputData;
						outputData[dataIndex] = convertedData[dataIndex];
						break;

					case HttpHelper.Array:
						outputData = (!outputData) ? [] : outputData;
						outputData[dataIndex] = convertedData[dataIndex];
						break;
				}
			}

		}

		return outputData;
	}
}
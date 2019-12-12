import { Injectable } from '@angular/core';
import { ConfigService } from "./config.service";

@Injectable({
  providedIn: 'root'
})
export class RouterService {
	constructor(protected config: ConfigService) { }

	/**
	 *
	 * @param routeName
	 * @param routeParameters
	 */
	public generateUrl(routeName: string, routeParameters: object = {}) : any {
		let routeUrl = null;

		for (let configRouteName in this.config.routing) {
			if (configRouteName === routeName) {
				let routeConfig = this.config.routing[configRouteName];
				let routeUrl = routeConfig.path;
				let routeUrlParamsReplacers = this.prepareParamsReplacersForUrl(routeUrl, routeConfig.defaults, routeParameters);

				if (routeUrlParamsReplacers) {
					for (let paramName in routeUrlParamsReplacers) {
						routeUrl = routeUrl.replace('{' + paramName + '}', routeUrlParamsReplacers[paramName]);
					}
				}

				return routeUrl;
			}
		}

		return routeUrl;
	}

	private prepareParamsReplacersForUrl(url: string, defaultParams: object = {}, externalParams: object = {}) {
		let urlArray = url.split('/');
		let urlParams = [];
		let paramsReplacers = [];

		urlArray.forEach(function(urlPart) {
			if (urlPart.substr(0, 1) === '{') {
				urlParams.push(urlPart.substr(1, urlPart.length - 2));
			}
		});

		if (urlParams) {
			urlParams.forEach(function(urlParamName) {
				if (externalParams && typeof externalParams[urlParamName] !== 'undefined') { // param passed in variable;
					paramsReplacers[urlParamName] = externalParams[urlParamName];
				} else if (defaultParams && defaultParams[urlParamName]) { // param not passed, but default exists;
					paramsReplacers[urlParamName] = defaultParams[urlParamName];
				} else { // param not passed and defaults not exits;
					paramsReplacers[urlParamName] = 'null';
				}
			});
		}

		return paramsReplacers;
	}
}

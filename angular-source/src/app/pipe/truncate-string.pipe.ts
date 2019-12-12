import {Pipe, PipeTransform} from "@angular/core";

@Pipe({ name:'truncateString' })
export class TruncateStringPipe implements PipeTransform {

	/**
	 * @param string - operated string
	 * @param maxLength - limit
	 * @param useWordBoundary - using word boundary
	 */
	transform(string: string = '', maxLength: number = 20, useWordBoundary: boolean = true): string {
		return this.truncate(string, maxLength, useWordBoundary);
	}

	protected truncate(string: string = '', maxLength: number = 20, useWordBoundary: boolean = true): string {
		if (string.length > maxLength) {
			let substring = string.substr(0, maxLength - 1);

			if (useWordBoundary && string.indexOf(' ') !== -1) {
				return substring.substr(0, substring.lastIndexOf(' ')) + "&hellip;";
			} else {
				return substring + "(&hellip;)";
			}
		}

		return string;
	}

}
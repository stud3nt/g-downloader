import {Pipe, PipeTransform} from "@angular/core";

@Pipe({ name:'movieLength' })
export class MovieLengthPipe implements PipeTransform {

	transform(seconds: number = 0): any {
	    let h = Math.floor(seconds / 60 / 60);
	    let m = Math.floor(seconds / 60);
	    let s = (seconds % 60);

		let hourString = (h).toString().padStart(2, '0');
		let minuteString = (m).toString().padStart(2, '0');
		let secondString = (s).toString().padStart(2, '0');


		return (h > 0)
            ? hourString+':'+minuteString+':'+secondString
            : minuteString+':'+secondString;
	}

}
